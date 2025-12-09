<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_location_settings', function (Blueprint $table) {
            // Permite reservas con stock negativo (no recomendado en productivo)
            $table->boolean('allow_negative_stock')
                ->default(false)
                ->after('allow_mixed_skus');

            // Porcentaje máximo del stock físico que puede reservarse (null = sin límite)
            $table->unsignedTinyInteger('max_reservation_percentage')
                ->nullable()
                ->after('allow_negative_stock');
        });
    }

    public function down(): void
    {
        Schema::table('stock_location_settings', function (Blueprint $table) {
            $table->dropColumn(['allow_negative_stock', 'max_reservation_percentage']);
        });
    }
};
