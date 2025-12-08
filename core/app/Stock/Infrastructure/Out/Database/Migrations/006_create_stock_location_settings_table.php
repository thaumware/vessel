<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_location_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('location_id')->unique();
            $table->integer('max_quantity')->nullable();
            $table->decimal('max_weight', 12, 3)->nullable();
            $table->decimal('max_volume', 12, 3)->nullable();
            $table->json('allowed_item_types')->nullable();
            $table->boolean('allow_mixed_lots')->default(true);
            $table->boolean('allow_mixed_skus')->default(true);
            $table->boolean('fifo_enforced')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('workspace_id')->nullable()->index();
            $table->json('meta')->nullable();
            
            // Auditoría estándar
            $table->uuid('created_by_id')->nullable()->index();
            $table->string('created_by_type', 100)->nullable();
            
            $table->timestamps();

            $table->index('location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_location_settings');
    }
};
