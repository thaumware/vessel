<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portal_origins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255)->unique();
            $table->string('direction', 1024);
            $table->string('type', 50);
            $table->boolean('is_active')->default(true);
            $table->uuid('workspace_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('portals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('has_portal_id');
            $table->string('has_portal_type', 255);
            $table->uuid('portal_origin_id');
            $table->uuid('external_id');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['has_portal_id', 'portal_origin_id']);
            $table->unique(['has_portal_id', 'external_id', 'portal_origin_id'], 'portals_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portals');
        Schema::dropIfExists('portal_origins');
    }
};
