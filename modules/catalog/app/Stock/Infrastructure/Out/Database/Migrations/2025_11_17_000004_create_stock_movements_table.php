<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            // Kardex-style movements table
            $table->uuid('id')->primary();
            $table->uuid('movement_id')->nullable()->index(); // external id / idempotency

            $table->string('sku')->index();

            // Polymorphic UUID columns for external modules (uuid morph)
            $table->uuid('location_from_id')->nullable()->index();
            $table->string('location_from_type')->nullable()->index();

            $table->uuid('location_to_id')->nullable()->index();
            $table->string('location_to_type')->nullable()->index();

            $table->integer('quantity');
            $table->integer('balance_after')->nullable(); // balance after applying movement (for that location)

            $table->string('movement_type', 64)->nullable(); // e.g. 'in','out','transfer','adjustment'
            $table->string('reference')->nullable(); // external reference
            $table->uuid('user_id')->nullable();

            $table->uuid('workspace_id')->nullable();

            $table->json('meta')->nullable(); // extra payload

            $table->dateTime('created_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
