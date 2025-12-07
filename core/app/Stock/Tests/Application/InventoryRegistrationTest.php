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
 * End-to-end style test: registra inventario inicial desde JSON y genera un CSV de kardex.
 * Valida que usamos itemId (sin SKU) y que los saldos quedan correctos por item/ubicación.
 */
class InventoryRegistrationTest extends StockTestCase
{
    public function test_register_inventory_from_json_and_generate_kardex_csv(): void
    {
        $fixturesDir = __DIR__ . '/../Support/data';
        $jsonPayload = $this->readFixture($fixturesDir . '/inventory_seed.json');
        $entries = json_decode($jsonPayload, true, 512, JSON_THROW_ON_ERROR);

        $movementRepo = new InMemoryMovementRepository();
        $stockItemRepo = new InMemoryStockItemRepository(loadFromFile: false);
        $service = new StockMovementService($movementRepo, $stockItemRepo);

        foreach ($entries as $entry) {
            $movement = new Movement(
                id: $entry['movement_id'],
                type: MovementType::RECEIPT,
                itemId: $entry['item_id'],
                locationId: $entry['location_id'],
                quantity: (float)$entry['quantity'],
                createdAt: new DateTimeImmutable($entry['timestamp'])
            );

            $result = $service->process($movement);
            $this->assertTrue($result->isSuccess(), 'Movement should be processed');
        }

        // Validar saldos finales por item/ubicacion
        $item100 = $stockItemRepo->findByItemAndLocation('ITEM-100', 'WH-1');
        $this->assertNotNull($item100);
        $this->assertSame(70.0, $item100->getQuantity());

        $item200 = $stockItemRepo->findByItemAndLocation('ITEM-200', 'WH-2');
        $this->assertNotNull($item200);
        $this->assertSame(10.0, $item200->getQuantity());

        // Generar CSV estilo kardex (fecha,item_id,location_id,tipo,cantidad,balance)
        $balances = [];
        $lines = ['movement_id,date,item_id,location_id,type,quantity,balance'];
        foreach ($movementRepo->all() as $m) {
            $key = $m->getItemId() . '|' . $m->getLocationId();
            $delta = $m->getQuantity() * $m->getType()->getQuantityMultiplier();
            $balances[$key] = ($balances[$key] ?? 0) + $delta;

            $lines[] = implode(',', [
                $m->getId(),
                $m->getCreatedAt()->format('Y-m-d H:i:s'),
                $m->getItemId(),
                $m->getLocationId(),
                $m->getType()->value,
                (string)$m->getQuantity(),
                (string)$balances[$key],
            ]);
        }

        $csv = implode("\n", $lines);

        $expectedCsv = $this->readFixture($fixturesDir . '/kardex_expected.csv');

        $normalizedCsv = str_replace("\r\n", "\n", $csv);
        $normalizedExpected = str_replace("\r\n", "\n", $expectedCsv);
        $this->assertSame(
          rtrim($normalizedExpected, "\r\n"),
          rtrim($normalizedCsv, "\r\n")
        );
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
