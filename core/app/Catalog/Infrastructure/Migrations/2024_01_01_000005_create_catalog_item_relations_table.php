<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_item_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('item_id')->index();         // Item principal
            $table->uuid('related_item_id')->index(); // Item relacionado
            
            // Tipos de relación:
            // - 'component'     : related_item es componente de item (repuesto de máquina)
            // - 'compatible'    : related_item es compatible con item
            // - 'accessory'     : related_item es accesorio de item
            // - 'replacement'   : related_item reemplaza a item
            // - 'variant_of'    : related_item es variante de item (color diferente)
            // - 'bundle'        : related_item forma parte de un bundle/kit
            // - 'requires'      : item requiere related_item
            // - 'recommended'   : related_item es recomendado con item
            // - 'similar'       : items similares
            // - 'upgrade'       : related_item es upgrade de item
            $table->string('relation_type')->index();
            
            $table->integer('quantity')->default(1);  // Cantidad (ej: 4 ruedas por auto)
            $table->boolean('is_required')->default(false); // ¿Es obligatorio?
            $table->integer('sort_order')->default(0);
            $table->json('meta')->nullable();         // Datos adicionales
            
            $table->uuid('workspace_id')->nullable()->index();
            $table->timestamps();
            
            // Evitar duplicados de la misma relación
            $table->unique(['item_id', 'related_item_id', 'relation_type'], 'item_relations_unique');
            
            $table->foreign('item_id')
                ->references('id')
                ->on('catalog_items')
                ->onDelete('cascade');
                
            $table->foreign('related_item_id')
                ->references('id')
                ->on('catalog_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_item_relations');
    }
};
