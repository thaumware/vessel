<?php

namespace App\Catalog\Tests\Feature;

use App\Uom\Infrastructure\Out\Database\Seeders\UomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemUomValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--path' => 'app/Catalog/Infrastructure/Migrations', '--realpath' => false]);
        $this->artisan('migrate', ['--path' => 'app/Uom/Infrastructure/Out/Database/Migrations', '--realpath' => false]);

        $this->seed(UomSeeder::class);
    }

    public function test_accepts_uom_id_uuid(): void
    {
        $payload = [
            'name' => 'Item con UOM ID',
            'description' => 'Test uom by id',
            'uom_id' => 'uom-kg',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/items/create', $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.uom_id', 'uom-kg');
    }

    public function test_accepts_uom_code_and_resolves_id(): void
    {
        $payload = [
            'name' => 'Item con UOM code',
            'description' => 'Test uom by code',
            'uom_id' => 'kg',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/items/create', $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.uom_id', 'uom-kg');
    }

    public function test_rejects_unknown_uom(): void
    {
        $payload = [
            'name' => 'Item con UOM invalido',
            'uom_id' => 'nope',
        ];

        $response = $this->postJson('/api/v1/items/create', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['uom_id']);
    }
}
