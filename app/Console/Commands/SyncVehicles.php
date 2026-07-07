<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\CarapisClient;
use App\Services\VehicleMapper;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;

class SyncVehicles extends Command
{
    /** Sequential fallback attempts for a page whose concurrent fetch failed. */
    private const PAGE_ATTEMPTS = 3;

    private const RETRY_WAIT_SECONDS = 20;

    protected $signature = 'vehicles:sync
        {--pages=0 : Maximum number of pages to fetch (0 = all pages)}
        {--start-page=1 : Page to resume from}
        {--page-size=500 : Records per API request}
        {--concurrency=4 : Pages fetched in parallel}';

    protected $description = 'Import available Encar vehicles from the Carapis catalog API';

    private array $filters = ['source' => 'encar', 'available_only' => 'true'];

    /** Set when the API reports quota exhaustion (HTTP 429); retrying is pointless. */
    private bool $throttled = false;

    public function handle(CarapisClient $carapis): int
    {
        $pageSize = (int) $this->option('page-size');
        $maxPages = (int) $this->option('pages');
        $startPage = max(1, (int) $this->option('start-page'));
        $concurrency = max(1, (int) $this->option('concurrency'));

        $syncStart = now();
        $imported = 0;
        $failedPages = [];

        // The first page is fetched alone to learn the total page count.
        $first = $this->fetchPageWithRetries($carapis, $startPage, $pageSize);

        if ($first === null) {
            $this->error("Page {$startPage} failed repeatedly. Aborting.");

            return self::FAILURE;
        }

        $totalPages = $first['pages'];
        $imported += $this->store($first['results'], $syncStart);
        $this->info("Page {$startPage}/{$totalPages} — {$imported} vehicles synced");

        $endPage = $maxPages > 0
            ? min($totalPages, $startPage + $maxPages - 1)
            : $totalPages;

        $remaining = range($startPage + 1, max($startPage + 1, $endPage));
        $remaining = array_filter($remaining, fn (int $page) => $page <= $endPage);

        foreach (array_chunk($remaining, $concurrency) as $batch) {
            $responses = $carapis->vehiclesConcurrently($batch, $pageSize, $this->filters);

            foreach ($batch as $page) {
                $payload = $responses[$page] ?? $this->fetchPageWithRetries($carapis, $page, $pageSize);

                if ($payload === null) {
                    $failedPages[] = $page;

                    if ($this->throttled) {
                        break;
                    }

                    $this->warn("Page {$page}/{$totalPages} skipped after repeated failures.");

                    continue;
                }

                $imported += $this->store($payload['results'], $syncStart);
                $this->info("Page {$page}/{$totalPages} — {$imported} vehicles synced");
            }

            if ($this->throttled) {
                $nextPage = min($failedPages);
                $this->error("API quota exhausted (HTTP 429). Stopping. Resume later with: php artisan vehicles:sync --start-page={$nextPage}");
                break;
            }
        }

        $isFullRun = $maxPages === 0 && $startPage === 1;

        // Only a complete, gap-free run can prove a vehicle disappeared from the feed.
        if ($isFullRun && $failedPages === []) {
            $delisted = Vehicle::query()
                ->where('source_code', 'encar')
                ->where('is_available', true)
                ->where(fn ($query) => $query
                    ->where('last_synced_at', '<', $syncStart)
                    ->orWhereNull('last_synced_at'))
                ->update(['is_available' => false]);

            $this->info("Marked {$delisted} delisted vehicles as unavailable.");
        } elseif ($failedPages !== []) {
            $this->warn('Skipped delisting step because pages failed: '.implode(', ', $failedPages));
        } else {
            $this->warn('Partial sync (--pages/--start-page set): skipped delisting step.');
        }

        $this->info("Done. {$imported} vehicles upserted in ".$syncStart->diffForHumans(now(), true).'.');

        return $failedPages === [] ? self::SUCCESS : self::FAILURE;
    }

    private function store(array $items, CarbonInterface $syncedAt): int
    {
        $rows = array_map(
            fn (array $item) => VehicleMapper::listRow($item, $syncedAt),
            $items,
        );

        Vehicle::upsert($rows, uniqueBy: ['id']);

        return count($rows);
    }

    /**
     * @return array{count: int, pages: int, has_next: bool, results: array<int, array<string, mixed>>}|null
     */
    private function fetchPageWithRetries(CarapisClient $carapis, int $page, int $pageSize): ?array
    {
        for ($attempt = 1; $attempt <= self::PAGE_ATTEMPTS; $attempt++) {
            try {
                return $carapis->vehicles($page, $pageSize, $this->filters);
            } catch (RequestException $e) {
                if ($e->response->status() === 429) {
                    $this->throttled = true;

                    return null;
                }

                $this->warn("Page {$page} attempt {$attempt}/".self::PAGE_ATTEMPTS." failed: {$e->getMessage()}");

                if ($attempt < self::PAGE_ATTEMPTS) {
                    sleep(self::RETRY_WAIT_SECONDS * $attempt);
                }
            } catch (\Throwable $e) {
                $this->warn("Page {$page} attempt {$attempt}/".self::PAGE_ATTEMPTS." failed: {$e->getMessage()}");

                if ($attempt < self::PAGE_ATTEMPTS) {
                    sleep(self::RETRY_WAIT_SECONDS * $attempt);
                }
            }
        }

        return null;
    }
}
