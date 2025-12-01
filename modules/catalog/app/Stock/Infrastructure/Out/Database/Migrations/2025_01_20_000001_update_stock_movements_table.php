<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Actualizar tabla stock_movements para el nuevo diseño flexible
        Schema::table('stock_movements', function (Blueprint $table) {
            // Nuevas columnas
            $table->string('type', 32)->after('sku')->index()->comment('MovementType enum value');
            $table->string('status', 32)->default('pending')->after('type')->index()->comment('MovementStatus enum value');
            $table->uuid('location_id')->after('status')->index()->comment('Ubicación principal del movimiento');
            
            // Lot tracking
            $table->string('lot_number', 100)->nullable()->after('quantity')->index();
            $table->date('expiration_date')->nullable()->after('lot_number');
            
            // References
            $table->string('reference_type', 50)->nullable()->after('balance_after')->comment('Tipo de documento: order, purchase_order, adjustment, etc');
            $table->string('reference_id', 100)->nullable()->after('reference_type')->index();
            $table->text('reason')->nullable()->after('reference_id');
            
            // Source/Destination for transfers
            $table->uuid('source_location_id')->nullable()->after('reason');
            $table->uuid('destination_location_id')->nullable()->after('source_location_id');
            
            // Rename user_id to performed_by for clarity
            $table->renameColumn('user_id', 'performed_by');
            
            // Drop old columns que se reemplazan
            $table->dropColumn(['location_from_id', 'location_from_type', 'location_to_id', 'location_to_type', 'movement_type', 'reference', 'movement_id']);
            
            // Indexes
            $table->index(['sku', 'location_id']);
            $table->index(['type', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Restaurar columnas originales
            $table->uuid('movement_id')->nullable()->after('id')->index();
            $table->uuid('location_from_id')->nullable()->after('sku');
            $table->string('location_from_type')->nullable()->after('location_from_id');
            $table->uuid('location_to_id')->nullable()->after('location_from_type');
            $table->string('location_to_type')->nullable()->after('location_to_id');
            $table->string('movement_type', 64)->nullable()->after('balance_after');
            $table->string('reference')->nullable()->after('movement_type');
            
            $table->renameColumn('performed_by', 'user_id');
            
            // Drop nuevas columnas
            $table->dropColumn([
                'type', 'status', 'location_id', 'lot_number', 'expiration_date',
                'reference_type', 'reference_id', 'reason', 'source_location_id', 'destination_location_id'
            ]);
            
            // Drop indexes
            $table->dropIndex(['sku', 'location_id']);
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['reference_type', 'reference_id']);
        });
    }
};
