<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_lots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('lot_number', 100)->unique()->comment('Número de lote único');
            $table->string('sku', 100)->index();
            
            // Dates
            $table->date('expiration_date')->nullable()->index();
            $table->date('production_date')->nullable();
            $table->date('reception_date')->nullable();
            
            // Supplier
            $table->uuid('supplier_id')->nullable()->index();
            $table->string('supplier_lot_number', 100)->nullable();
            
            // Status
            $table->string('status', 32)->default('active')->index()->comment('active, quarantine, expired, depleted');
            
            // Workspace
            $table->uuid('workspace_id')->nullable()->index();
            
            // Meta
            $table->json('meta')->nullable();
            
            // Timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            // Indexes
            $table->index(['sku', 'status']);
            $table->index(['status', 'expiration_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_lots');
    }
};
