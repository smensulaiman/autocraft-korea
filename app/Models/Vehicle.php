<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property array<int, array{position?: int, url?: string|null, thumb_url?: string|null}>|null $photos
 */
class Vehicle extends Model
{
    private const VALUE_LABELS = [
        'auto' => 'Automatic', 'semi_auto' => 'Semi-automatic', 'cvt' => 'CVT', 'dct' => 'DCT',
        'plug_hybrid' => 'Plug-in hybrid', 'lpg' => 'LPG', 'suv' => 'SUV', 'usa' => 'USA',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'features' => 'array',
            'has_accident' => 'boolean',
            'has_simple_repair' => 'boolean',
            'has_recall' => 'boolean',
            'recall_fulfilled' => 'boolean',
            'inspection_passed' => 'boolean',
            'is_new_vehicle' => 'boolean',
            'is_verified' => 'boolean',
            'is_available' => 'boolean',
            'price_original' => 'decimal:2',
            'original_msrp' => 'decimal:2',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'detail_synced_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_slug', 'slug');
    }

    /**
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public static function label(?string $value): string
    {
        if ($value === null || $value === '' || $value === 'unknown') {
            return '—';
        }

        return self::VALUE_LABELS[$value] ?? ucfirst(str_replace('_', ' ', $value));
    }

    public function displayPriceUsd(): ?float
    {
        if ($this->price_usd === null) {
            return null;
        }

        $markup = 1 + (config('services.pricing.markup', 10) / 100);

        return round(((float) $this->price_usd) * $markup);
    }

    public function title(): string
    {
        return trim(($this->year ? $this->year.' ' : '').$this->brand_name.' '.$this->model_name);
    }

    public function thumbUrl(): ?string
    {
        return $this->resolveMediaUrl($this->thumb_url);
    }

    /**
     * @return array<int, array{url: ?string, thumb: ?string}>
     */
    public function photoUrls(): array
    {
        return collect($this->photos ?? [])
            ->sortBy('position')
            ->map(fn (array $photo) => [
                'url' => $this->resolveMediaUrl($photo['url'] ?? null),
                'thumb' => $this->resolveMediaUrl($photo['thumb_url'] ?? $photo['url'] ?? null),
            ])
            ->filter(fn (array $photo) => $photo['url'] !== null)
            ->values()
            ->all();
    }

    public function hasDetails(): bool
    {
        return $this->detail_synced_at !== null;
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim(config('services.carapis.url'), '/').$path;
    }
}
