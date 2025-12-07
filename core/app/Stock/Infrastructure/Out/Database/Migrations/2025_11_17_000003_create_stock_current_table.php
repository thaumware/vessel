<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_current', function (Blueprint $table) {
            // Use UUID PK for uniformity
            $table->uuid('id')->primary();
            $table->string('sku')->index();
            $table->uuid('location_id')->index();
            $table->string('location_type')->nullable()->index();
            $table->integer('quantity')->default(0);

            $table->uuid('workspace_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            // Ensure uniqueness for sku + location
            $table->unique(['sku', 'location_id', 'location_type'], 'stock_current_sku_location_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_current');
    }
};
