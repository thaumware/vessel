<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pivot para relación M:M entre Items y Terms (Taxonomy)
     * Permite asignar múltiples marcas, categorías, etc. a cada item
     */
    public function up(): void
    {
        Schema::create('catalog_item_terms', function (Blueprint $table) {
            $table->uuid('item_id');
            $table->uuid('term_id');
            $table->timestamps();

            $table->primary(['item_id', 'term_id']);

            // Avoid hard FK coupling; use indexes only to keep setup resilient to ordering
            $table->index('item_id');
            $table->index('term_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_item_terms');
    }
};
