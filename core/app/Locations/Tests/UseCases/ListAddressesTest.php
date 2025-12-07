<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\ListAddresses;
use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;
use PHPUnit\Framework\TestCase;

class ListAddressesTest extends TestCase
{
    /** @var AddressRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private ListAddresses $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AddressRepository::class);
        $this->useCase = new ListAddresses($this->repository);
    }

    public function test_returns_all_addresses_when_no_filter(): void
    {
        $addresses = [
            new Address(
                id: 'uuid-1',
                name: 'Spain',
                addressType: AddressType::COUNTRY,
            ),
            new Address(
                id: 'uuid-2',
                name: 'France',
                addressType: AddressType::COUNTRY,
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn($addresses);

        $result = $this->useCase->execute();

        $this->assertCount(2, $result);
        $this->assertEquals('Spain', $result[0]->getName());
        $this->assertEquals('France', $result[1]->getName());
    }

    public function test_returns_children_when_parent_id_provided(): void
    {
        $children = [
            new Address(
                id: 'madrid-uuid',
                name: 'Madrid',
                addressType: AddressType::STATE,
                parentAddressId: 'spain-uuid',
            ),
            new Address(
                id: 'barcelona-uuid',
                name: 'Cataluña',
                addressType: AddressType::STATE,
                parentAddressId: 'spain-uuid',
            ),
        ];

        $this->repository->expects($this->once())
            ->method('findByParentId')
            ->with('spain-uuid')
            ->willReturn($children);

        $result = $this->useCase->execute('spain-uuid');

        $this->assertCount(2, $result);
        $this->assertEquals('Madrid', $result[0]->getName());
        $this->assertEquals('Cataluña', $result[1]->getName());
    }

    public function test_returns_empty_array_when_no_addresses(): void
    {
        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->useCase->execute();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_returns_empty_array_when_no_children(): void
    {
        $this->repository->expects($this->once())
            ->method('findByParentId')
            ->with('parent-uuid')
            ->willReturn([]);

        $result = $this->useCase->execute('parent-uuid');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
