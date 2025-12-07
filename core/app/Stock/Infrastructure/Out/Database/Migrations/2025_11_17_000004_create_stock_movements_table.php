<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Core fields
            $table->string('sku')->index();
            $table->string('type', 32)->index()->comment('MovementType: RECEIPT, SHIPMENT, ADJUSTMENT_IN, etc');
            $table->string('status', 32)->default('pending')->index()->comment('MovementStatus: pending, completed, cancelled, failed');
            $table->uuid('location_id')->index()->comment('Primary location for the movement');
            $table->integer('quantity');
            $table->integer('balance_after')->nullable()->comment('Balance after applying movement');
            
            // Lot tracking
            $table->string('lot_number', 100)->nullable()->index();
            $table->date('expiration_date')->nullable();
            
            // References
            $table->string('reference_type', 50)->nullable()->comment('Document type: order, purchase_order, transfer, etc');
            $table->string('reference_id', 100)->nullable()->index();
            $table->text('reason')->nullable();
            
            // Transfer fields
            $table->uuid('source_location_id')->nullable()->comment('For TRANSFER_OUT');
            $table->uuid('destination_location_id')->nullable()->comment('For TRANSFER_IN');
            
            // Audit
            $table->uuid('performed_by')->nullable();
            $table->uuid('workspace_id')->nullable()->index();
            $table->json('meta')->nullable();
            
            // Timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->softDeletes();
            
            // Composite indexes
            $table->index(['sku', 'location_id']);
            $table->index(['type', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
