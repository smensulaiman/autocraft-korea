<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CarapisClient
{
    /**
     * Fetch one page of the vehicle catalog.
     *
     * @param  array<string, string>  $filters
     * @return array{count: int, pages: int, has_next: bool, results: array<int, array<string, mixed>>}
     */
    public function vehicles(int $page = 1, int $pageSize = 500, array $filters = []): array
    {
        return $this->configure(Http::createPendingRequest())
            ->retry(3, 5000, throw: false)
            ->get('/apix/catalog_api/vehicles/', [
                ...$filters,
                'page' => $page,
                'page_size' => $pageSize,
            ])
            ->throw()
            ->json();
    }

    /**
     * Fetch several catalog pages concurrently.
     *
     * @param  array<int, int>  $pages
     * @param  array<string, string>  $filters
     * @return array<int, array{count: int, pages: int, has_next: bool, results: array<int, array<string, mixed>>}|null>
     *                                                                                                                   Page payloads keyed by page number; null for pages that failed.
     */
    public function vehiclesConcurrently(array $pages, int $pageSize = 500, array $filters = []): array
    {
        $responses = Http::pool(fn (Pool $pool) => array_map(
            fn (int $page) => $this->configure($pool->as((string) $page))
                ->get('/apix/catalog_api/vehicles/', [
                    ...$filters,
                    'page' => $page,
                    'page_size' => $pageSize,
                ]),
            $pages,
        ));

        $results = [];

        foreach ($pages as $page) {
            $response = $responses[(string) $page] ?? null;

            $results[$page] = $response instanceof Response && $response->successful()
                ? $response->json()
                : null;
        }

        return $results;
    }

    /**
     * Fetch the full record for a single vehicle.
     *
     * @return array<string, mixed>
     */
    public function vehicle(string $id): array
    {
        return $this->configure(Http::createPendingRequest())
            ->timeout(60)
            ->retry(2, 3000, throw: false)
            ->get("/apix/catalog_api/vehicles/{$id}/")
            ->throw()
            ->json();
    }

    private function configure(PendingRequest $request): PendingRequest
    {
        return $request
            ->baseUrl(config('services.carapis.url'))
            ->withHeaders([
                'accept' => 'application/json',
                'x-api-key' => config('services.carapis.key'),
            ])
            ->timeout(300);
    }
}
