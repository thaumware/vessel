<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Estados de stock personalizables por workspace (workspace_id null = global)
        Schema::create('stock_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->nullable()->index(); // null = accesible por todos
            $table->string('code', 50)->index(); // reserved, damaged, lost, etc.
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['workspace_id', 'code']);
            $table->index(['workspace_id', 'is_active']);
        });

        // Reglas de comportamiento para cada estado (normalizado)
        Schema::create('stock_status_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('status_id')->index();
            $table->string('rule_type', 50); // allow_movements, blocks_availability, requires_approval
            $table->boolean('rule_value')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('status_id')->references('id')->on('stock_statuses')->onDelete('cascade');
            $table->unique(['status_id', 'rule_type']);
        });

        // Transiciones permitidas entre estados
        Schema::create('stock_status_transitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_status_id')->index();
            $table->uuid('to_status_id')->index();
            $table->boolean('requires_approval')->default(false);
            $table->string('condition', 100)->nullable(); // on_movement_complete, lot_expired, manual
            $table->integer('priority')->default(0); // orden de evaluación
            $table->timestamps();
            
            $table->foreign('from_status_id')->references('id')->on('stock_statuses')->onDelete('cascade');
            $table->foreign('to_status_id')->references('id')->on('stock_statuses')->onDelete('cascade');
            $table->unique(['from_status_id', 'to_status_id']);
        });

        // Eventos de estados (domain events)
        Schema::create('stock_status_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('status_id')->index();
            $table->string('event_type', 100); // status_created, status_activated, rule_changed
            $table->json('metadata')->nullable(); // datos del evento
            $table->uuid('triggered_by')->nullable(); // user_id
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('status_id')->references('id')->on('stock_statuses')->onDelete('cascade');
            $table->index(['status_id', 'event_type']);
            $table->index('occurred_at');
        });

        // Agregar status y polimorfismo a stock_items
        Schema::table('stock_items', function (Blueprint $table) {
            $table->uuid('status_id')->nullable()->after('reserved_quantity')->index();
            $table->string('item_type')->after('status_id')->index(); // lot, unit, batch (morph map)
            $table->uuid('item_id')->after('item_type')->index(); // polimórfico
            
            $table->foreign('status_id')->references('id')->on('stock_statuses')->onDelete('set null');
            $table->index(['item_type', 'item_id']);
        });

        // Historial de cambios de estado (seguimiento total)
        Schema::create('stock_status_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stock_item_id')->index();
            $table->uuid('from_status_id')->nullable()->index();
            $table->uuid('to_status_id')->index();
            $table->uuid('movement_id')->nullable()->index(); // si fue por movimiento
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid('changed_by')->nullable(); // user_id
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->onDelete('cascade');
            $table->foreign('from_status_id')->references('id')->on('stock_statuses')->onDelete('set null');
            $table->foreign('to_status_id')->references('id')->on('stock_statuses')->onDelete('restrict');
            $table->index(['stock_item_id', 'changed_at']);
        });

        // Tracking de status en movimientos
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->uuid('resulting_status_id')->nullable()->after('created_at')->index();
            $table->foreign('resulting_status_id')->references('id')->on('stock_statuses')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['resulting_status_id']);
            $table->dropColumn('resulting_status_id');
        });

        Schema::dropIfExists('stock_status_history');

        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn(['status_id', 'item_type', 'item_id']);
        });

        Schema::dropIfExists('stock_status_events');
        Schema::dropIfExists('stock_status_transitions');
        Schema::dropIfExists('stock_status_rules');
        Schema::dropIfExists('stock_statuses');
    }
};
