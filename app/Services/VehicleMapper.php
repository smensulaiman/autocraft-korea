<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class VehicleMapper
{
    /**
     * Map a catalog list item to a database-ready row for bulk upserts.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public static function listRow(array $item, CarbonInterface $syncedAt): array
    {
        return [
            'id' => $item['id'],
            'source_code' => $item['source_code'],
            'brand_name' => $item['brand_name'] ?? null,
            'brand_slug' => $item['brand_slug'] ?? null,
            'model_name' => $item['model_name'] ?? null,
            'model_slug' => $item['model_slug'] ?? null,
            'trim' => $item['trim'] ?? null,
            'year' => $item['year'] ?? null,
            'price_usd' => $item['price_usd'] ?? null,
            'mileage' => $item['mileage'] ?? null,
            'fuel_type' => $item['fuel_type'] ?? null,
            'transmission' => $item['transmission'] ?? null,
            'body_type' => $item['body_type'] ?? null,
            'color' => $item['color'] ?? null,
            'seller_type' => $item['seller_type'] ?? null,
            'region' => $item['region'] ?? null,
            'source_location' => $item['source_location'] ?? null,
            'has_accident' => $item['has_accident'] ?? null,
            'is_new_vehicle' => (bool) ($item['is_new_vehicle'] ?? false),
            'is_verified' => (bool) ($item['is_verified'] ?? false),
            'thumb_url' => $item['thumb']['url'] ?? null,
            'photos' => json_encode($item['photos'] ?? []),
            'photos_count' => $item['photos_count'] ?? count($item['photos'] ?? []),
            'first_seen_at' => self::timestamp($item['first_seen_at'] ?? null),
            'last_seen_at' => self::timestamp($item['last_seen_at'] ?? null),
            'is_available' => true,
            'last_synced_at' => $syncedAt->toDateTimeString(),
        ];
    }

    /**
     * Map a vehicle detail payload to model attributes (cast-aware).
     *
     * @param  array<string, mixed>  $detail
     * @return array<string, mixed>
     */
    public static function detailAttributes(array $detail): array
    {
        return [
            'generation' => $detail['generation'] ?? null,
            'price_original' => $detail['price_original'] ?? null,
            'price_original_currency' => $detail['price_original_currency'] ?? null,
            'original_msrp' => $detail['original_msrp'] ?? null,
            'engine_cc' => $detail['engine_cc'] ?? null,
            'seat_count' => $detail['seat_count'] ?? null,
            'drive_type' => $detail['drive_type'] ?? null,
            'has_simple_repair' => $detail['has_simple_repair'] ?? null,
            'has_recall' => $detail['has_recall'] ?? null,
            'recall_fulfilled' => $detail['recall_fulfilled'] ?? null,
            'warranty_type' => $detail['warranty_type'] ?? null,
            'inspection_passed' => $detail['inspection_passed'] ?? null,
            'owner_count' => $detail['owner_count'] ?? null,
            'description' => $detail['description'] ?? null,
            'features' => $detail['features'] ?? [],
            'listing_url' => $detail['listing_url'] ?? null,
            'listing_id' => $detail['listing_id'] ?? null,
            'vin' => $detail['vin'] ?? null,
            'vehicle_no' => $detail['vehicle_no'] ?? null,
            'photos' => $detail['photos'] ?? [],
            'photos_count' => count($detail['photos'] ?? []),
            'is_available' => (bool) ($detail['is_available'] ?? true),
            'detail_synced_at' => now(),
        ];
    }

    private static function timestamp(?string $value): ?string
    {
        return $value ? Carbon::parse($value)->toDateTimeString() : null;
    }
}
