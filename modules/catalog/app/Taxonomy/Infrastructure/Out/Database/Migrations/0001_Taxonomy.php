<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('taxonomy_vocabularies', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->string('slug', 255);

            $table->uuid('workspace_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

        });

        Schema::create('taxonomy_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->string('slug', 255);

            $table->uuid('vocabulary_id')->nullable();

            $table->uuid('workspace_id')->nullable();

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();

        });


        Schema::create('taxonomy_term_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('from_term_id');
            $table->uuid('to_term_id');

            $table->string('relation_type', 255)->nullable();

            $table->integer('depth')->default(0);

            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('taxonomy');
        Schema::dropIfExists('taxonomy_terms');
        Schema::dropIfExists('taxonomy_term_relations');
    }
};