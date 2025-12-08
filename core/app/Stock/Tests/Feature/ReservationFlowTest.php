<?php

declare(strict_types=1);

namespace App\Stock\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test del flujo completo de reservaciones.
 * 
 * Verifica:
 * 1. Validación previa (sin modificar estado)
 * 2. Creación de reserva
 * 3. Liberación de reserva
 * 4. Respeto de configuración de locación (max_reservation_percentage)
 * 5. Validación de stock insuficiente
 */
class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // TODO: Seed inicial de:
        // - Item en catálogo
        // - Locación
        // - Stock inicial (100 unidades)
        // - Configuración de locación (80% max_reservation)
    }

    public function test_can_validate_reservation_before_creating(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // Arrange: Item con 100 unidades, 20 reservadas, 80 disponibles
        
        // Act: Validar reserva de 30
        $response = $this->postJson('/api/v1/stock/reservations/validate', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 30,
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'can_reserve' => true,
            'available_quantity' => 80,
            'reserved_quantity' => 20,
            'total_quantity' => 100,
            'max_reservation_allowed' => 80, // 80% de 100
            'errors' => [],
        ]);
    }

    public function test_can_create_reservation(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // Act
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 30,
            'reference_type' => 'sales_order',
            'reference_id' => 'SO-2024-001',
            'reason' => 'Reserva para venta',
        ]);

        // Assert
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'new_reserved_quantity' => 50, // 20 + 30
            'new_available_quantity' => 50, // 80 - 30
        ]);
        
        $this->assertNotNull($response->json('reservation_id'));
    }

    public function test_cannot_reserve_more_than_available(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // Arrange: Disponible = 80
        
        // Act: Intentar reservar 90
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 90,
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        
        $this->assertStringContainsString('insuficiente', $response->json('errors')[0]);
    }

    public function test_cannot_exceed_max_reservation_percentage(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // Arrange: Total = 100, Max = 80% = 80 unidades, Ya reservadas = 20
        
        // Act: Intentar reservar 70 más (total sería 90, excede 80)
        $response = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 70,
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('límite de reserva', $response->json('errors')[0]);
    }

    public function test_can_release_reservation(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // Arrange: Reservadas = 50
        
        // Act: Liberar 20
        $response = $this->postJson('/api/v1/stock/reservations/release', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 20,
            'reference_id' => 'SO-2024-001',
            'reason' => 'Orden cancelada',
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'new_reserved_quantity' => 30, // 50 - 20
            'new_available_quantity' => 70, // 50 + 20
        ]);
    }

    public function test_cannot_release_more_than_reserved(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // Arrange: Reservadas = 50
        
        // Act: Intentar liberar 60
        $response = $this->postJson('/api/v1/stock/reservations/release', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 60,
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertStringContainsString('suficiente cantidad reservada', $response->json('errors')[0]);
    }

    public function test_validation_includes_item_and_location_info(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // Act
        $response = $this->postJson('/api/v1/stock/reservations/validate', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 10,
        ]);

        // Assert
        $response->assertStatus(200);
        $this->assertNotNull($response->json('item_info'));
        $this->assertNotNull($response->json('location_info'));
        
        // Debe incluir info del catálogo
        $this->assertArrayHasKey('name', $response->json('item_info'));
        
        // Debe incluir info de la locación
        $this->assertArrayHasKey('name', $response->json('location_info'));
    }

    public function test_complete_flow_validate_reserve_release(): void
    {
        $this->markTestIncomplete('Requiere seed de datos');
        
        // 1. Validar
        $validation = $this->postJson('/api/v1/stock/reservations/validate', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 25,
        ]);
        $validation->assertStatus(200);
        $validation->assertJson(['can_reserve' => true]);

        // 2. Reservar
        $reservation = $this->postJson('/api/v1/stock/reservations/reserve', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 25,
            'reference_id' => 'TEST-FLOW-001',
        ]);
        $reservation->assertStatus(201);
        $reservation->assertJson(['success' => true]);
        
        $reservationId = $reservation->json('reservation_id');
        $this->assertNotNull($reservationId);

        // 3. Liberar
        $release = $this->postJson('/api/v1/stock/reservations/release', [
            'item_id' => 'ITEM-001',
            'location_id' => 'WAREHOUSE-MAIN',
            'quantity' => 25,
            'reference_id' => 'TEST-FLOW-001',
        ]);
        $release->assertStatus(200);
        $release->assertJson(['success' => true]);
        
        // Verificar que volvió al estado original
        $this->assertEquals(
            $validation->json('available_quantity'),
            $release->json('new_available_quantity')
        );
    }
}
