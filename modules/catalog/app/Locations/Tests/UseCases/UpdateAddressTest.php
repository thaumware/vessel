<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\UpdateAddress;
use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;
use PHPUnit\Framework\TestCase;

class UpdateAddressTest extends TestCase
{
    /** @var AddressRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private UpdateAddress $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AddressRepository::class);
        $this->useCase = new UpdateAddress($this->repository);
    }

    public function test_returns_null_when_address_not_found(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with('non-existent')
            ->willReturn(null);

        $result = $this->useCase->execute('non-existent', ['name' => 'Updated']);

        $this->assertNull($result);
    }

    public function test_updates_name(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Old Name',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with('test-uuid')
            ->willReturn($original);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($this->callback(function (Address $address) {
                return $address->getName() === 'New Name';
            }));

        $result = $this->useCase->execute('test-uuid', ['name' => 'New Name']);

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('New Name', $result->getName());
    }

    public function test_updates_address_type(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Madrid',
            addressType: AddressType::CITY,
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($original);

        $this->repository->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute('test-uuid', ['address_type' => 'state']);

        $this->assertEquals('state', $result->getAddressType());
    }

    public function test_updates_parent_address_id(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Madrid',
            addressType: AddressType::CITY,
            parentAddressId: null,
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($original);

        $this->repository->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute('test-uuid', ['parent_address_id' => 'spain-uuid']);

        $this->assertEquals('spain-uuid', $result->getParentAddressId());
    }

    public function test_can_set_parent_address_id_to_null(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Madrid',
            addressType: AddressType::CITY,
            parentAddressId: 'spain-uuid',
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($original);

        $this->repository->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute('test-uuid', ['parent_address_id' => null]);

        $this->assertNull($result->getParentAddressId());
    }

    public function test_updates_description(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
            description: 'Old description',
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($original);

        $this->repository->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute('test-uuid', ['description' => 'New description']);

        $this->assertEquals('New description', $result->getDescription());
    }

    public function test_preserves_unmodified_fields(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
            parentAddressId: 'europe-uuid',
            description: 'A country',
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($original);

        $this->repository->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute('test-uuid', ['name' => 'España']);

        $this->assertEquals('España', $result->getName());
        $this->assertEquals('country', $result->getAddressType());
        $this->assertEquals('europe-uuid', $result->getParentAddressId());
        $this->assertEquals('A country', $result->getDescription());
    }

    public function test_ignores_invalid_address_type(): void
    {
        $original = new Address(
            id: 'test-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($original);

        $this->repository->expects($this->once())
            ->method('update');

        $result = $this->useCase->execute('test-uuid', ['address_type' => 'invalid_type']);

        // Debe mantener el tipo original
        $this->assertEquals('country', $result->getAddressType());
    }
}
