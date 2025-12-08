<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identificación y tipo de movimiento
            $table->string('sku')->index();
            $table->string('movement_type', 64)->index();
            $table->string('status', 32)->nullable()->index()->comment('MovementStatus opcional');

            // Ubicaciones (origen/destino)
            $table->uuid('location_from_id')->nullable()->index();
            $table->uuid('location_to_id')->nullable()->index();

            // Cantidades
            $table->integer('quantity');
            $table->integer('balance_after')->nullable()->comment('Balance after applying movement');

            // Referencias y auditoría
            $table->string('reference', 255)->nullable();
            $table->uuid('user_id')->nullable()->comment('DEPRECATED: usar created_by_id');
            $table->uuid('workspace_id')->nullable()->index();
            $table->json('meta')->nullable();

            // Auditoría estándar
            $table->uuid('created_by_id')->nullable()->index();
            $table->string('created_by_type', 100)->nullable()->comment('Polymorphic: User, System, Integration, etc.');
            
            // Timestamps estándar
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
