<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('portal_origins')) {
            Schema::create('portal_origins', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name')->unique();
                $table->string('direction');
                $table->string('type');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('portals')) {
            Schema::create('portals', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('has_portal_id');
                $table->string('has_portal_type');
                $table->uuid('portal_origin_id');
                $table->string('external_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index('has_portal_id');
                $table->index('portal_origin_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portals');
        Schema::dropIfExists('portal_origins');
    }
};
