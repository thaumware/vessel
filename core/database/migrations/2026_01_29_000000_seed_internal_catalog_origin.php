<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\Uuid;

/**
 * Seed the internal_catalog portal origin
 * 
 * This origin is required for Stock module to link items to the internal catalog.
 */
return new class extends Migration {
    public function up(): void
    {
        // Check if origin already exists
        $exists = DB::table('portal_origins')
            ->where('name', 'internal_catalog')
            ->exists();

        if (!$exists) {
            DB::table('portal_origins')->insert([
                'id' => (string) Uuid::v4(),
                'name' => 'internal_catalog',
                'direction' => 'catalog_items', // Source table
                'type' => 'table',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('portal_origins')
            ->where('name', 'internal_catalog')
            ->delete();
    }
};
