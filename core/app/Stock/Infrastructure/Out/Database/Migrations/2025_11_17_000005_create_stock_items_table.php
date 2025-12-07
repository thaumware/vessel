<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku');
            $table->string('catalog_item_id')->nullable();
            $table->string('catalog_origin')->nullable();
            $table->uuid('location_id');
            $table->string('location_type')->default('warehouse');
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('reserved_quantity', 15, 4)->default(0);
            $table->string('lot_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('serial_number')->nullable();
            $table->uuid('workspace_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('sku');
            $table->index('location_id');
            $table->index('catalog_item_id');
            $table->index('catalog_origin');
            $table->index('workspace_id');
            $table->index('lot_number');
            $table->index('expiration_date');
            
            // Unique constraint for SKU + Location (one stock item per SKU per location)
            $table->unique(['sku', 'location_id'], 'stock_items_sku_location_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
