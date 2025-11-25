<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->uuid('vocabulary_id')->index();
            $table->uuid('workspace_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vocabulary_id')
                ->references('id')
                ->on('taxonomy_vocabularies')
                ->onDelete('cascade');

            $table->unique(['slug', 'vocabulary_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_terms');
    }
};
