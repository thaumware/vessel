<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_location_settings', function (Blueprint $table) {
            // Unidad de medida para la capacidad (storage unit)
            // Ejemplo: Si max_quantity=1000 y storage_uom_id='kg', 
            // la ubicación acepta hasta 1000kg
            $table->uuid('storage_uom_id')
                ->nullable()
                ->after('max_quantity')
                ->comment('Unidad de medida para max_quantity');
            
            // Índice para búsquedas por UoM
            $table->index('storage_uom_id');
            
            // FK hacia stock_units (UoM)
            $table->foreign('storage_uom_id')
                ->references('id')
                ->on('stock_units')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_location_settings', function (Blueprint $table) {
            $table->dropForeign(['storage_uom_id']);
            $table->dropIndex(['storage_uom_id']);
            $table->dropColumn('storage_uom_id');
        });
    }
};
