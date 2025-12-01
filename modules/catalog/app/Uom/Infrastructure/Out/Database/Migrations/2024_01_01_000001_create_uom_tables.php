<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categorías de unidades de medida
        Schema::create('uom_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->uuid('workspace_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // Unidades de medida
        Schema::create('uom_measures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('symbol', 20)->nullable();
            $table->string('category', 50)->nullable()->index();
            $table->boolean('is_base')->default(false);
            $table->text('description')->nullable();
            $table->uuid('workspace_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            // FK opcional a categorías (permite categorías custom)
            $table->foreign('category')
                ->references('code')
                ->on('uom_categories')
                ->onDelete('set null');
        });

        // Conversiones entre unidades
        Schema::create('uom_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_measure_id');
            $table->uuid('to_measure_id');
            $table->decimal('factor', 20, 10);
            $table->enum('operation', ['mul', 'div', 'add', 'sub'])->default('mul');
            $table->uuid('workspace_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('from_measure_id')
                ->references('id')
                ->on('uom_measures')
                ->onDelete('cascade');

            $table->foreign('to_measure_id')
                ->references('id')
                ->on('uom_measures')
                ->onDelete('cascade');

            // Evitar duplicados de conversión
            $table->unique(['from_measure_id', 'to_measure_id', 'workspace_id'], 'uom_conversion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
        Schema::dropIfExists('uom_measures');
        Schema::dropIfExists('uom_categories');
    }
};
