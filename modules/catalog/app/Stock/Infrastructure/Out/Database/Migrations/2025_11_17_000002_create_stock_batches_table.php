<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            // Use UUID PK
            $table->uuid('id')->primary();
            $table->string('sku')->index();
            $table->uuid('location_id')->index();
            $table->integer('quantity')->default(0);
            $table->string('lot_number')->nullable();

            $table->uuid('workspace_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            // Optional: allow multiple batches with same sku/location if lot differs
            $table->index(['sku', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
