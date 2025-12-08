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
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('item_id', 36)->index();
            $table->string('location_id', 36)->index();
            $table->decimal('quantity', 15, 4);
            $table->string('reserved_by', 100)->index(); // user-id, system, etc
            $table->string('reference_type', 50)->index(); // order, project, loan
            $table->string('reference_id', 100)->index();
            $table->enum('status', ['active', 'released', 'expired'])->default('active')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('released_at')->nullable();

            // Índices compuestos para queries comunes
            $table->index(['item_id', 'location_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
