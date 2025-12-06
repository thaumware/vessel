<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_item_identifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id')->index();
            $table->uuid('variant_id')->nullable()->index(); // NULL = identificador del item base
            $table->string('type'); // sku, ean, upc, gtin, mpn, supplier_code, custom
            $table->string('value')->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            // Un item/variant no puede tener duplicados del mismo tipo
            $table->unique(['item_id', 'variant_id', 'type'], 'item_identifiers_unique');
            
            // Índice para búsquedas rápidas por valor
            $table->index(['type', 'value'], 'item_identifiers_type_value');
            
            $table->foreign('item_id')
                ->references('id')
                ->on('catalog_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_item_identifiers');
    }
};
