<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_units', function (Blueprint $table) {
            // Use UUID primary key for consistency
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');

            // Multi-tenant / workspace support
            $table->uuid('workspace_id')->nullable();

            // Auditoría estándar
            $table->uuid('created_by_id')->nullable()->index();
            $table->string('created_by_type', 100)->nullable();

            // Use nullable datetimes to match Taxonomy style
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_units');
    }
};
