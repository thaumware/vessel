<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('locations_locations', function (Blueprint $table) {
            $table->uuid('parent_id')->nullable()->after('address_id')->index();
            
            // Self-referencing FK para jerarquía
            $table->foreign('parent_id')
                ->references('id')
                ->on('locations_locations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('locations_locations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
