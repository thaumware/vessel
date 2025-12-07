<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove parent_id column from catalog_terms table.
 * 
 * The hierarchy is now managed through the catalog_term_relations table
 * which provides more flexibility for different relation types:
 * - parent: hierarchical parent-child relationship
 * - related: non-hierarchical association
 * - synonym: equivalent terms
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('catalog_terms', function (Blueprint $table) {
            // Drop foreign key if exists
            if (Schema::hasColumn('catalog_terms', 'parent_id')) {
                // Try to drop foreign key (may not exist)
                try {
                    $table->dropForeign(['parent_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                
                $table->dropColumn('parent_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('catalog_terms', function (Blueprint $table) {
            if (!Schema::hasColumn('catalog_terms', 'parent_id')) {
                $table->uuid('parent_id')->nullable()->after('vocabulary_id');
                $table->foreign('parent_id')
                    ->references('id')
                    ->on('catalog_terms')
                    ->onDelete('set null');
            }
        });
    }
};
