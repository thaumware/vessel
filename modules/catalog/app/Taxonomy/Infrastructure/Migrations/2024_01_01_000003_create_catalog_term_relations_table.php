<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_term_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('from_term_id')->index();
            $table->uuid('to_term_id')->index();
            $table->string('relation_type')->default('parent'); // parent, related, synonym
            $table->integer('depth')->default(1);
            $table->timestamps();

            $table->foreign('from_term_id')
                ->references('id')
                ->on('catalog_terms')
                ->onDelete('cascade');

            $table->foreign('to_term_id')
                ->references('id')
                ->on('catalog_terms')
                ->onDelete('cascade');

            $table->unique(['from_term_id', 'to_term_id', 'relation_type'], 'term_relation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_term_relations');
    }
};
