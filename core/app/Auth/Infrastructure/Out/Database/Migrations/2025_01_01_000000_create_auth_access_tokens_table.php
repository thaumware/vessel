<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_access_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('token', 64)->unique();
            $table->string('workspace_id')->nullable()->index();
            $table->string('scope', 50)->default('all'); // 'all', 'own'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_access_tokens');
    }
};
