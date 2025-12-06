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

class InventoryMultitenantKardexTest extends StockTestCase
{
    public function test_generates_kardex_per_workspace_and_isolates_balances(): void
    {
        $fixturesDir = __DIR__ . '/../Support/data';
        $entries = $this->readJson($fixturesDir . '/inventory_seed_multitenant.json');

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
                workspaceId: $entry['workspace_id'] ?? null,
                meta: array_merge($entry['meta'] ?? [], [
                    'uom' => $entry['uom'] ?? null,
                    'taxonomy' => $entry['taxonomy'] ?? null,
                ]),
                createdAt: new DateTimeImmutable($entry['timestamp'])
            );

            $result = $service->process($movement);
            $this->assertTrue($result->isSuccess(), 'Movement should be processed');
        }

        // Generar kardex por workspace usando search(workspaceId)
        $csvPlant01 = $this->buildKardexCsv($movementRepo, 'plant-01');
        $expectedPlant01 = $this->readFixture($fixturesDir . '/kardex_expected_multitenant_plant01.csv');
        $this->assertSame(
            rtrim(str_replace("\r\n", "\n", $expectedPlant01), "\n"),
            rtrim($csvPlant01, "\n")
        );

        $csvPlant02 = $this->buildKardexCsv($movementRepo, 'plant-02');
        $expectedPlant02 = $this->readFixture($fixturesDir . '/kardex_expected_multitenant_plant02.csv');
        $this->assertSame(
            rtrim(str_replace("\r\n", "\n", $expectedPlant02), "\n"),
            rtrim($csvPlant02, "\n")
        );

        // Aislamiento: los balances de plant-01 no se mezclan con plant-02
        $this->assertSame(45.0, $stockItemRepo->findByItemAndLocation('VALVE-100', 'WH-A')?->getQuantity());
        $this->assertSame(17.0, $stockItemRepo->findByItemAndLocation('PUMP-200', 'WH-A')?->getQuantity());
        $this->assertSame(27.0, $stockItemRepo->findByItemAndLocation('SENSOR-TX', 'DC-1')?->getQuantity());
    }

    private function buildKardexCsv(InMemoryMovementRepository $repo, string $workspaceId): string
    {
        $lines = ['movement_id,date,item_id,location_id,type,quantity,uom,balance,reference_type,reference_id'];
        $balances = [];

        // Movements ya están en orden de inserción; si se requiere orden por fecha, se puede ordenar aquí.
        foreach ($repo->search(new \App\Stock\Domain\ValueObjects\MovementSearchCriteria(workspaceId: $workspaceId, sortDesc: false)) as $m) {
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

        return implode("\n", $lines);
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
