<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_code', 40)->index();

            $table->string('brand_name')->nullable();
            $table->string('brand_slug')->nullable()->index();
            $table->string('model_name')->nullable();
            $table->string('model_slug')->nullable()->index();
            $table->string('trim')->nullable();
            $table->string('generation', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();

            $table->unsignedInteger('price_usd')->nullable()->index();
            $table->decimal('price_original', 14, 2)->nullable();
            $table->string('price_original_currency', 10)->nullable();
            $table->decimal('original_msrp', 14, 2)->nullable();

            $table->unsignedInteger('mileage')->nullable()->index();
            $table->unsignedInteger('engine_cc')->nullable();
            $table->unsignedTinyInteger('seat_count')->nullable();
            $table->string('fuel_type', 40)->nullable()->index();
            $table->string('transmission', 40)->nullable()->index();
            $table->string('body_type', 40)->nullable()->index();
            $table->string('color', 60)->nullable()->index();
            $table->string('drive_type', 20)->nullable();

            $table->string('seller_type', 40)->nullable();
            $table->string('region', 100)->nullable()->index();
            $table->string('source_location')->nullable();

            $table->boolean('has_accident')->nullable();
            $table->boolean('has_simple_repair')->nullable();
            $table->boolean('has_recall')->nullable();
            $table->boolean('recall_fulfilled')->nullable();
            $table->string('warranty_type', 100)->nullable();
            $table->boolean('inspection_passed')->nullable();
            $table->unsignedTinyInteger('owner_count')->nullable();
            $table->boolean('is_new_vehicle')->default(false);
            $table->boolean('is_verified')->default(false);

            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('listing_url')->nullable();
            $table->string('listing_id', 60)->nullable();
            $table->string('vin', 40)->nullable();
            $table->string('vehicle_no', 40)->nullable();

            $table->string('thumb_url')->nullable();
            $table->json('photos')->nullable();
            $table->unsignedSmallInteger('photos_count')->default(0);

            $table->boolean('is_available')->default(true)->index();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamp('detail_synced_at')->nullable();

            $table->timestamps();

            $table->fullText(['brand_name', 'model_name', 'trim']);
        });
    }

    public function down(): void
    {
        Schema::drop('vehicles');
    }
};
