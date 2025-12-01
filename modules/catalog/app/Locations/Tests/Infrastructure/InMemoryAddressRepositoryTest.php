<?php

namespace App\Locations\Tests\Infrastructure;

use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\ValueObjects\AddressType;
use App\Locations\Infrastructure\Out\InMemory\InMemoryAddressRepository;
use PHPUnit\Framework\TestCase;

class InMemoryAddressRepositoryTest extends TestCase
{
    private InMemoryAddressRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAddressRepository();
    }

    public function test_save_and_find_by_id(): void
    {
        $address = new Address(
            id: 'test-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->save($address);
        $found = $this->repository->findById('test-uuid');

        $this->assertNotNull($found);
        $this->assertEquals('Spain', $found->getName());
        $this->assertEquals('country', $found->getAddressType());
    }

    public function test_find_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findById('non-existent');

        $this->assertNull($result);
    }

    public function test_find_all(): void
    {
        $address1 = new Address(
            id: 'uuid-1',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );
        $address2 = new Address(
            id: 'uuid-2',
            name: 'France',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->save($address1);
        $this->repository->save($address2);

        $all = $this->repository->findAll();

        $this->assertCount(2, $all);
    }

    public function test_find_all_returns_empty_array_when_no_addresses(): void
    {
        $all = $this->repository->findAll();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    public function test_find_by_parent_id(): void
    {
        $spain = new Address(
            id: 'spain-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );
        $madrid = new Address(
            id: 'madrid-uuid',
            name: 'Madrid',
            addressType: AddressType::STATE,
            parentAddressId: 'spain-uuid',
        );
        $barcelona = new Address(
            id: 'barcelona-uuid',
            name: 'Cataluña',
            addressType: AddressType::STATE,
            parentAddressId: 'spain-uuid',
        );
        $paris = new Address(
            id: 'paris-uuid',
            name: 'Paris',
            addressType: AddressType::STATE,
            parentAddressId: 'france-uuid',
        );

        $this->repository->save($spain);
        $this->repository->save($madrid);
        $this->repository->save($barcelona);
        $this->repository->save($paris);

        $children = $this->repository->findByParentId('spain-uuid');

        $this->assertCount(2, $children);
        $names = array_map(fn($a) => $a->getName(), $children);
        $this->assertContains('Madrid', $names);
        $this->assertContains('Cataluña', $names);
    }

    public function test_find_by_parent_id_returns_empty_when_no_children(): void
    {
        $spain = new Address(
            id: 'spain-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->save($spain);

        $children = $this->repository->findByParentId('spain-uuid');

        $this->assertIsArray($children);
        $this->assertEmpty($children);
    }

    public function test_find_by_id_with_children(): void
    {
        $spain = new Address(
            id: 'spain-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );
        $madrid = new Address(
            id: 'madrid-uuid',
            name: 'Madrid',
            addressType: AddressType::STATE,
            parentAddressId: 'spain-uuid',
        );
        $barcelona = new Address(
            id: 'barcelona-uuid',
            name: 'Cataluña',
            addressType: AddressType::STATE,
            parentAddressId: 'spain-uuid',
        );

        $this->repository->save($spain);
        $this->repository->save($madrid);
        $this->repository->save($barcelona);

        $result = $this->repository->findByIdWithChildren('spain-uuid');

        $this->assertNotNull($result);
        $this->assertArrayHasKey('address', $result);
        $this->assertArrayHasKey('children', $result);
        $this->assertEquals('Spain', $result['address']->getName());
        $this->assertCount(2, $result['children']);
    }

    public function test_find_by_id_with_children_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByIdWithChildren('non-existent');

        $this->assertNull($result);
    }

    public function test_update(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->save($original);

        $updated = new Address(
            id: 'test-uuid',
            name: 'España',
            addressType: AddressType::COUNTRY,
            description: 'Updated description',
        );

        $this->repository->update($updated);

        $found = $this->repository->findById('test-uuid');
        $this->assertEquals('España', $found->getName());
        $this->assertEquals('Updated description', $found->getDescription());
    }

    public function test_delete(): void
    {
        $address = new Address(
            id: 'test-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->save($address);
        $this->assertNotNull($this->repository->findById('test-uuid'));

        $this->repository->delete($address);

        $this->assertNull($this->repository->findById('test-uuid'));
    }

    public function test_clear(): void
    {
        $address1 = new Address(
            id: 'uuid-1',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );
        $address2 = new Address(
            id: 'uuid-2',
            name: 'France',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->save($address1);
        $this->repository->save($address2);

        $this->assertCount(2, $this->repository->findAll());

        $this->repository->clear();

        $this->assertEmpty($this->repository->findAll());
    }
}
