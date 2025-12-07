<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tipos de ubicación configurables (workspace_id null = global)
        Schema::create('location_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->nullable()->index(); // null = accesible por todos
            $table->string('code', 50)->index(); // warehouse, staging, quarantine, production
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['workspace_id', 'code']);
        });

        // Jerarquía en locations (parent_id ya existe en 0003)
        Schema::table('locations_locations', function (Blueprint $table) {
            $table->uuid('type_id')->nullable()->after('workspace_id')->index();
            $table->integer('level')->default(0)->after('address_id')->index();
            $table->string('path', 500)->nullable()->after('level')->index(); // /uuid1/uuid2/uuid3
            
            $table->foreign('type_id')->references('id')->on('location_types')->onDelete('set null');
        });

        // Reglas configurables polimórficas (location o location_type)
        Schema::create('location_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id')->nullable()->index();
            $table->string('locable_type')->index(); // Location, LocationType (morph map)
            $table->uuid('locable_id')->index(); // polimórfico
            $table->string('rule_key', 100); // value object: max_capacity, allowed_item_types, etc
            $table->text('rule_value'); // valor de la regla
            $table->json('metadata')->nullable(); // config adicional
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['locable_type', 'locable_id']);
            $table->index(['workspace_id', 'is_active']);
            $table->index(['rule_key', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_rules');

        Schema::table('locations_locations', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
            $table->dropColumn(['type_id', 'level', 'path']);
        });

        Schema::dropIfExists('location_types');
    }
};
