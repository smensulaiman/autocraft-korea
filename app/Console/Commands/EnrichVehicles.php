<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\CarapisClient;
use App\Services\VehicleMapper;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;

class EnrichVehicles extends Command
{
    /** Stop the run early when the API looks down. */
    private const MAX_CONSECUTIVE_FAILURES = 5;

    protected $signature = 'vehicles:enrich {--limit=300 : Vehicles to enrich per run}';

    protected $description = 'Fetch full detail records (chassis, grade, engine, all photos) for vehicles that only have catalog data';

    public function handle(CarapisClient $carapis): int
    {
        $vehicles = Vehicle::query()
            ->available()
            ->whereNull('detail_synced_at')
            ->orderByDesc('first_seen_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($vehicles->isEmpty()) {
            $this->info('Nothing to enrich.');

            return self::SUCCESS;
        }

        $enriched = 0;
        $delisted = 0;
        $consecutiveFailures = 0;

        foreach ($vehicles as $vehicle) {
            try {
                $detail = $carapis->vehicle($vehicle->id);
                $vehicle->fill(VehicleMapper::detailAttributes($detail))->save();
                $enriched++;
                $consecutiveFailures = 0;
            } catch (RequestException $e) {
                if ($e->response->status() === 404) {
                    // Gone from the source; hide it and stop re-trying.
                    $vehicle->forceFill(['is_available' => false, 'detail_synced_at' => now()])->save();
                    $delisted++;
                    $consecutiveFailures = 0;

                    continue;
                }

                $consecutiveFailures++;
            } catch (\Throwable) {
                $consecutiveFailures++;
            }

            if ($consecutiveFailures >= self::MAX_CONSECUTIVE_FAILURES) {
                $this->warn('API failing repeatedly; stopping this run early.');
                break;
            }

            usleep(250_000);
        }

        $this->info("Enriched {$enriched} vehicles, delisted {$delisted}.");

        return self::SUCCESS;
    }
}
