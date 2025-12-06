<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_item_specifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id')->index();
            $table->uuid('variant_id')->nullable()->index(); // NULL = spec del item base
            
            $table->string('key')->index();           // e.g. 'color', 'size', 'weight', 'duration'
            $table->text('value');                     // e.g. 'red', 'XL', '500', '2 hours'
            $table->string('data_type')->default('string'); // string, number, boolean, json, date
            $table->string('unit')->nullable();        // e.g. 'kg', 'cm', 'hours' (display unit)
            $table->string('group')->nullable();       // e.g. 'dimensions', 'colors', 'technical'
            $table->integer('sort_order')->default(0);
            
            $table->uuid('workspace_id')->nullable()->index();
            $table->timestamps();
            
            // Un item/variant no puede tener la misma key duplicada
            $table->unique(['item_id', 'variant_id', 'key'], 'item_specs_unique');
            
            $table->foreign('item_id')
                ->references('id')
                ->on('catalog_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_item_specifications');
    }
};
