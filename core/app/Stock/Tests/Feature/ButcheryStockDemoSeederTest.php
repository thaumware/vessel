<?php

declare(strict_types=1);

namespace App\Stock\Tests\Feature;

use App\Catalog\Infrastructure\Out\Database\Seeders\ButcheryCatalogSeeder;
use App\Stock\Infrastructure\Out\Database\Seeders\ButcheryStockDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ButcheryStockDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_stock_for_butchery(): void
    {
        $this->seed(ButcheryCatalogSeeder::class);
        $this->seed(ButcheryStockDemoSeeder::class);

        $this->assertEquals(5, DB::table('locations_locations')->count());
        $this->assertEquals(3, DB::table('stock_statuses')->count());
        $this->assertEquals(9, DB::table('stock_lots')->count());
        $this->assertEquals(9, DB::table('stock_items')->count());
        $this->assertEquals(9, DB::table('stock_current')->count());
        $this->assertEquals(4, DB::table('stock_reservations')->count());
        $this->assertEquals(9, DB::table('stock_movements')->count());

        $displayId = DB::table('locations_locations')->where('name', 'Mostrador')->value('id');
        $freezerId = DB::table('locations_locations')->where('name', 'Freezer')->value('id');

        $this->assertDatabaseHas('stock_items', [
            'sku' => 'VAC-ASADO',
            'location_id' => $displayId,
            'quantity' => 12,
        ]);

        $this->assertDatabaseHas('stock_items', [
            'sku' => 'POL-PECHU-QA',
            'location_id' => $freezerId,
            'quantity' => 6,
        ]);

        $this->assertDatabaseHas('stock_lots', [
            'lot_number' => 'LOT-POL-QA-001',
            'status' => 'quarantine',
        ]);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(ButcheryCatalogSeeder::class);
        $this->seed(ButcheryStockDemoSeeder::class);
        $this->seed(ButcheryStockDemoSeeder::class);

        $this->assertEquals(9, DB::table('stock_items')->count());
        $this->assertEquals(9, DB::table('stock_current')->count());
        $this->assertEquals(4, DB::table('stock_reservations')->count());
        $this->assertEquals(9, DB::table('stock_movements')->count());
    }
}
