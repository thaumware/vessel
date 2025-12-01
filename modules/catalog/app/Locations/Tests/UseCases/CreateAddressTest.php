<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\CreateAddress;
use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;
use PHPUnit\Framework\TestCase;

class CreateAddressTest extends TestCase
{
    /** @var AddressRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private CreateAddress $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AddressRepository::class);
        $this->useCase = new CreateAddress($this->repository);
    }

    public function test_creates_address_with_required_fields(): void
    {
        $id = 'test-uuid';
        $data = [
            'name' => 'Spain',
            'address_type' => 'country',
        ];

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Address $address) use ($id) {
                return $address->getId() === $id
                    && $address->getName() === 'Spain'
                    && $address->getAddressType() === 'country';
            }));

        $result = $this->useCase->execute($id, $data);

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals($id, $result->getId());
        $this->assertEquals('Spain', $result->getName());
        $this->assertEquals('country', $result->getAddressType());
    }

    public function test_creates_address_with_all_fields(): void
    {
        $id = 'test-uuid';
        $data = [
            'name' => 'Madrid',
            'address_type' => 'city',
            'parent_address_id' => 'parent-uuid',
            'description' => 'Capital of Spain',
        ];

        $this->repository->expects($this->once())
            ->method('save');

        $result = $this->useCase->execute($id, $data);

        $this->assertEquals('Madrid', $result->getName());
        $this->assertEquals('city', $result->getAddressType());
        $this->assertEquals('parent-uuid', $result->getParentAddressId());
        $this->assertEquals('Capital of Spain', $result->getDescription());
    }

    public function test_uses_default_type_when_invalid(): void
    {
        $id = 'test-uuid';
        $data = [
            'name' => 'Test',
            'address_type' => 'invalid_type',
        ];

        $this->repository->expects($this->once())
            ->method('save');

        $result = $this->useCase->execute($id, $data);

        // Debe usar STREET como default
        $this->assertEquals('street', $result->getAddressType());
    }

    public function test_creates_address_without_optional_fields(): void
    {
        $id = 'test-uuid';
        $data = [
            'name' => 'Test Address',
        ];

        $this->repository->expects($this->once())
            ->method('save');

        $result = $this->useCase->execute($id, $data);

        $this->assertNull($result->getParentAddressId());
        $this->assertNull($result->getDescription());
    }
}
