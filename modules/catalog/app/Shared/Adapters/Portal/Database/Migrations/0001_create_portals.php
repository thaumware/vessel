<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        Schema::create('portal_origins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // URL o tabla local
            $table->string('direction', 1024)->nullable();

            // Tipo: 'table', 'http'
            $table->string('type', 255)->nullable();

            $table->boolean('is_active')->default(true);

            $table->uuid('workspace_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('portals', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ID del modelo que tiene portal
            $table->nullableUuidMorphs('has_portal', 'portals_has_portal_ix');

            // Tipo: 'Item', 'Term'
            $table->string('portal_type')->nullable();
            // ID remoto
            $table->uuid('external_id')->nullable();

            // Metadatos adicionales del portal como snapshot, sync status, etc.
            $table->json('metadata')->nullable();

            $table->uuid('portal_origin_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->unique(['has_portal_id', 'external_id', 'portal_origin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portals');
        Schema::dropIfExists('portal_origins');
    }
};
