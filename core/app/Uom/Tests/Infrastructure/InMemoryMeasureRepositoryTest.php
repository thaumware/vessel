<?php

namespace App\Uom\Tests\Infrastructure;

use App\Uom\Domain\Entities\Measure;
use App\Uom\Infrastructure\Out\InMemory\InMemoryMeasureRepository;
use App\Uom\Tests\UomTestCase;

class InMemoryMeasureRepositoryTest extends UomTestCase
{
    private InMemoryMeasureRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryMeasureRepository();
    }

    public function test_save_and_find_by_id(): void
    {
        $measure = new Measure(
            id: 'test-uuid',
            code: 'kg',
            name: 'Kilogram',
            symbol: 'kg',
            category: 'mass',
            isBase: true,
        );

        $this->repository->save($measure);
        $found = $this->repository->findById('test-uuid');

        $this->assertNotNull($found);
        $this->assertEquals('kg', $found->getCode());
        $this->assertEquals('Kilogram', $found->getName());
    }

    public function test_find_by_code(): void
    {
        $measure = new Measure(
            id: 'test-uuid',
            code: 'kg',
            name: 'Kilogram',
        );

        $this->repository->save($measure);
        $found = $this->repository->findById('kg'); // Search by code

        $this->assertNotNull($found);
        $this->assertEquals('test-uuid', $found->getId());
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById('non-existent');

        $this->assertNull($result);
    }

    public function test_find_all(): void
    {
        $measure1 = new Measure(id: 'uuid-1', code: 'kg', name: 'Kilogram');
        $measure2 = new Measure(id: 'uuid-2', code: 'g', name: 'Gram');

        $this->repository->save($measure1);
        $this->repository->save($measure2);

        $all = $this->repository->findAll();

        $this->assertCount(2, $all);
    }

    public function test_find_all_returns_empty_when_no_measures(): void
    {
        $all = $this->repository->findAll();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    public function test_find_by_category(): void
    {
        $kg = new Measure(id: 'uuid-1', code: 'kg', name: 'Kilogram', category: 'mass');
        $g = new Measure(id: 'uuid-2', code: 'g', name: 'Gram', category: 'mass');
        $m = new Measure(id: 'uuid-3', code: 'm', name: 'Meter', category: 'length');

        $this->repository->save($kg);
        $this->repository->save($g);
        $this->repository->save($m);

        $massMeasures = $this->repository->findByCategory('mass');

        $this->assertCount(2, $massMeasures);
        $codes = array_map(fn($m) => $m->getCode(), $massMeasures);
        $this->assertContains('kg', $codes);
        $this->assertContains('g', $codes);
    }

    public function test_find_base_measures(): void
    {
        $kg = new Measure(id: 'uuid-1', code: 'kg', name: 'Kilogram', isBase: true);
        $g = new Measure(id: 'uuid-2', code: 'g', name: 'Gram', isBase: false);
        $m = new Measure(id: 'uuid-3', code: 'm', name: 'Meter', isBase: true);

        $this->repository->save($kg);
        $this->repository->save($g);
        $this->repository->save($m);

        $baseMeasures = $this->repository->findBaseMeasures();

        $this->assertCount(2, $baseMeasures);
        $codes = array_map(fn($m) => $m->getCode(), $baseMeasures);
        $this->assertContains('kg', $codes);
        $this->assertContains('m', $codes);
    }

    public function test_update(): void
    {
        $original = new Measure(id: 'test-uuid', code: 'kg', name: 'Kilogram');
        $this->repository->save($original);

        $updated = new Measure(
            id: 'test-uuid',
            code: 'kg',
            name: 'Kilogramo',
            description: 'Updated description',
        );
        $this->repository->update($updated);

        $found = $this->repository->findById('test-uuid');
        $this->assertEquals('Kilogramo', $found->getName());
        $this->assertEquals('Updated description', $found->getDescription());
    }

    public function test_delete(): void
    {
        $measure = new Measure(id: 'test-uuid', code: 'kg', name: 'Kilogram');
        $this->repository->save($measure);

        $this->assertNotNull($this->repository->findById('test-uuid'));

        $this->repository->delete('test-uuid');

        $this->assertNull($this->repository->findById('test-uuid'));
    }

    public function test_clear(): void
    {
        $measure1 = new Measure(id: 'uuid-1', code: 'kg', name: 'Kilogram');
        $measure2 = new Measure(id: 'uuid-2', code: 'g', name: 'Gram');

        $this->repository->save($measure1);
        $this->repository->save($measure2);

        $this->assertCount(2, $this->repository->findAll());

        $this->repository->clear();

        $this->assertEmpty($this->repository->findAll());
    }

    public function test_load_base_data(): void
    {
        $repository = new InMemoryMeasureRepository(loadBaseData: true);

        $all = $repository->findAll();

        // Should have all measures from the data file
        $this->assertGreaterThan(50, count($all));

        // Check some specific measures exist
        $kg = $repository->findById('kg');
        $this->assertNotNull($kg);
        $this->assertEquals('Kilogramo', $kg->getName());

        $m = $repository->findById('m');
        $this->assertNotNull($m);
        $this->assertEquals('Metro', $m->getName());
    }

    public function test_load_base_data_categories(): void
    {
        $repository = new InMemoryMeasureRepository(loadBaseData: true);

        $massMeasures = $repository->findByCategory('mass');
        $this->assertGreaterThan(3, count($massMeasures));

        $lengthMeasures = $repository->findByCategory('length');
        $this->assertGreaterThan(3, count($lengthMeasures));

        $dataMeasures = $repository->findByCategory('data');
        $this->assertGreaterThan(3, count($dataMeasures));
    }
}
