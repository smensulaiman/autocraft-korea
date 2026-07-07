<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SyncBrandLogos extends Command
{
    private const SOURCE_URL = 'https://raw.githubusercontent.com/avto-dev/vehicle-logotypes/master/src/vehicle-logotypes.json';

    /** Encar brand slugs that differ from the logotype dataset's keys. */
    private const ALIASES = [
        'benz' => 'mercedes-benz',
        'kg-mobility' => 'ssangyong',
        'kg-mobility-ssangyong' => 'ssangyong',
        'kgm' => 'ssangyong',
        'renault-korea' => 'renault',
        'baic-yinxiang' => 'baic-motor',
        'dfsk' => 'dongfeng',
    ];

    /** Manufacturer origin per brand slug; anything unlisted stays 'other'. */
    private const ORIGINS = [
        'korea' => ['hyundai', 'kia', 'genesis', 'ssangyong', 'kg-mobility', 'kg-mobility-ssangyong', 'kgm', 'renault-korea'],
        'germany' => ['audi', 'benz', 'mercedes-benz', 'bmw', 'porsche', 'volkswagen', 'smart', 'maybach'],
        'japan' => ['toyota', 'lexus', 'honda', 'nissan', 'infiniti', 'subaru', 'suzuki', 'daihatsu', 'mazda', 'mitsubishi'],
        'usa' => ['ford', 'chevrolet', 'cadillac', 'jeep', 'lincoln', 'tesla', 'hummer', 'chrysler', 'dodge', 'gmc'],
        'europe' => ['bentley', 'fiat', 'jaguar', 'lamborghini', 'land-rover', 'maserati', 'mclaren', 'mini', 'peugeot', 'polestar', 'renault', 'rolls-royce', 'saab', 'volvo', 'ferrari', 'alfa-romeo', 'citroen', 'skoda', 'lotus', 'aston-martin'],
        'china' => ['byd', 'baic-yinxiang', 'dfsk', 'geely', 'chery', 'dongfeng', 'xpeng'],
    ];

    protected $signature = 'brands:sync-logos {--force : Re-download logos that already exist locally}';

    protected $description = 'Populate the brands table with logos from the avto-dev/vehicle-logotypes dataset, stored in public/logos';

    public function handle(): int
    {
        $logos = Http::timeout(30)
            ->retry(2, 2000, throw: false)
            ->get(self::SOURCE_URL)
            ->throw()
            ->json();

        File::ensureDirectoryExists(public_path('logos'));

        $stockBrands = Vehicle::query()
            ->whereNotNull('brand_slug')
            ->selectRaw('brand_slug, min(brand_name) as brand_name')
            ->groupBy('brand_slug')
            ->orderBy('brand_slug')
            ->get();

        $rows = [];
        $unmatched = [];
        $downloaded = 0;

        foreach ($stockBrands as $stockBrand) {
            $key = self::ALIASES[$stockBrand->brand_slug] ?? $stockBrand->brand_slug;
            $remoteUrl = $logos[$key]['logotype']['uri'] ?? null;
            $logoUrl = null;

            if ($remoteUrl === null) {
                $unmatched[] = $stockBrand->brand_slug;
            } else {
                $logoUrl = $this->download($stockBrand->brand_slug, $remoteUrl, $downloaded)
                    ?? $remoteUrl; // Fall back to the CDN if the download fails.
            }

            $rows[] = [
                'slug' => $stockBrand->brand_slug,
                'name' => $stockBrand->brand_name,
                'origin' => $this->origin($stockBrand->brand_slug),
                'logo_url' => $logoUrl,
            ];
        }

        Brand::upsert($rows, uniqueBy: ['slug'], update: ['name', 'origin', 'logo_url']);

        $withLogo = count($rows) - count($unmatched);
        $this->info(count($rows)." brands stored, {$withLogo} with a logo, {$downloaded} newly downloaded.");

        if ($unmatched !== []) {
            $this->warn('No logo found for: '.implode(', ', $unmatched));
        }

        return self::SUCCESS;
    }

    private function origin(string $slug): string
    {
        foreach (self::ORIGINS as $origin => $slugs) {
            if (in_array($slug, $slugs, true)) {
                return $origin;
            }
        }

        return 'other';
    }

    private function download(string $slug, string $remoteUrl, int &$downloaded): ?string
    {
        $extension = pathinfo(parse_url($remoteUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
        $filename = "{$slug}.{$extension}";
        $path = public_path("logos/{$filename}");

        if (! File::exists($path) || $this->option('force')) {
            try {
                $response = Http::timeout(30)->retry(2, 1000, throw: false)->get($remoteUrl)->throw();
                File::put($path, $response->body());
                $downloaded++;
            } catch (\Throwable $e) {
                $this->warn("Download failed for {$slug}: {$e->getMessage()}");

                return null;
            }
        }

        return "/logos/{$filename}";
    }
}
