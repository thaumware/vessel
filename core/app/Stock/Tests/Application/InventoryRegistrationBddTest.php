<?php

namespace App\Stock\Tests\Application;

use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Services\StockMovementService;
use App\Stock\Domain\ValueObjects\MovementType;
use App\Stock\Infrastructure\Out\InMemory\InMemoryMovementRepository;
use App\Stock\Infrastructure\Out\InMemory\InMemoryStockItemRepository;
use App\Stock\Tests\StockTestCase;
use DateTimeImmutable;
use RuntimeException;

/**
 * Escenario BDD ampliado: inventario de repuestos/mantenimientos con relaciones
 * de ubicaciones, referencias (PO/WO/Kanban/RMA), UOM y taxonomía embebida en meta.
 * Usa itemId como identificador canónico (sin SKU).
 */
class InventoryRegistrationBddTest extends StockTestCase
{
    public function test_registers_complex_inventory_and_generates_kardex(): void
    {
        $fixturesDir = __DIR__ . '/../Support/data';
        $entries = $this->readJson($fixturesDir . '/inventory_seed_complex.json');

        $movementRepo = new InMemoryMovementRepository();
        $stockItemRepo = new InMemoryStockItemRepository(loadFromFile: false);
        $service = new StockMovementService($movementRepo, $stockItemRepo);

        foreach ($entries as $entry) {
            $movement = new Movement(
                id: $entry['movement_id'],
                type: MovementType::from($entry['movement_type']),
                itemId: $entry['item_id'],
                locationId: $entry['location_id'],
                quantity: (float)$entry['quantity'],
                referenceType: $entry['reference_type'] ?? null,
                referenceId: $entry['reference_id'] ?? null,
                sourceLocationId: $entry['source_location_id'] ?? null,
                destinationLocationId: $entry['destination_location_id'] ?? null,
                workspaceId: $entry['workspace_id'] ?? null,
                meta: array_merge(
                    $entry['meta'] ?? [],
                    [
                        'uom' => $entry['uom'] ?? null,
                        'taxonomy' => $entry['taxonomy'] ?? null,
                    ]
                ),
                createdAt: new DateTimeImmutable($entry['timestamp'])
            );

            $result = $service->process($movement);
            $this->assertTrue($result->isSuccess(), 'Movement should be processed');
        }

        // Validar saldos finales por item/ubicacion
        $this->assertSame(80.0, $stockItemRepo->findByItemAndLocation('BEARING-6204', 'WH-MAIN')?->getQuantity());
        $this->assertSame(300.0, $stockItemRepo->findByItemAndLocation('BOLT-M8X30', 'WH-MAIN')?->getQuantity());
        $this->assertSame(200.0, $stockItemRepo->findByItemAndLocation('BOLT-M8X30', 'LINE-01')?->getQuantity());
        $this->assertSame(45.0, $stockItemRepo->findByItemAndLocation('LUBE-ISO46', 'WH-MAIN')?->getQuantity());

        // Generar CSV estilo kardex (balance por item/ubicación con UOM y referencias)
        $balances = [];
        $lines = ['movement_id,date,item_id,location_id,type,quantity,uom,balance,reference_type,reference_id'];
        foreach ($movementRepo->all() as $m) {
            $key = $m->getItemId() . '|' . $m->getLocationId();
            $delta = $m->getQuantity() * $m->getType()->getQuantityMultiplier();
            $balances[$key] = ($balances[$key] ?? 0) + $delta;
            $uom = $m->getMeta()['uom'] ?? '';

            $lines[] = implode(',', [
                $m->getId(),
                $m->getCreatedAt()->format('Y-m-d H:i:s'),
                $m->getItemId(),
                $m->getLocationId(),
                $m->getType()->value,
                (string)$m->getQuantity(),
                $uom,
                (string)$balances[$key],
                $m->getReferenceType() ?? '',
                $m->getReferenceId() ?? '',
            ]);
        }

        $csv = implode("\n", $lines);
        $expected = $this->readFixture($fixturesDir . '/kardex_expected_complex.csv');

        $normalized = str_replace("\r\n", "\n", $csv);
        $normalizedExpected = str_replace("\r\n", "\n", $expected);
        $this->assertSame(rtrim($normalizedExpected, "\r\n"), rtrim($normalized, "\r\n"));

        // Sanity: metadatos/UOM y taxonomía quedan persistidos en movements
        $first = $movementRepo->findById('mov-101');
        $this->assertNotNull($first);
        $this->assertSame('ea', $first->getMeta()['uom'] ?? null);
        $this->assertContains('spare', $first->getMeta()['taxonomy'] ?? []);
    }

    private function readJson(string $path): array
    {
        $content = $this->readFixture($path);
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    private function readFixture(string $path): string
    {
        $data = @file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException("Fixture not found: {$path}");
        }
        return $data;
    }
}
