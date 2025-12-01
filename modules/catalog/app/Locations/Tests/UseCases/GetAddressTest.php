<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\GetAddress;
use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;
use PHPUnit\Framework\TestCase;

class GetAddressTest extends TestCase
{
    /** @var AddressRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private GetAddress $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AddressRepository::class);
        $this->useCase = new GetAddress($this->repository);
    }

    public function test_returns_address_when_found(): void
    {
        $address = new Address(
            id: 'test-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with('test-uuid')
            ->willReturn($address);

        $result = $this->useCase->execute('test-uuid');

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('test-uuid', $result->getId());
        $this->assertEquals('Spain', $result->getName());
    }

    public function test_returns_null_when_not_found(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with('non-existent')
            ->willReturn(null);

        $result = $this->useCase->execute('non-existent');

        $this->assertNull($result);
    }

    public function test_returns_address_with_children_when_requested(): void
    {
        $address = new Address(
            id: 'spain-uuid',
            name: 'Spain',
            addressType: AddressType::COUNTRY,
        );

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
            ->method('findByIdWithChildren')
            ->with('spain-uuid')
            ->willReturn([
                'address' => $address,
                'children' => $children,
            ]);

        $result = $this->useCase->execute('spain-uuid', withChildren: true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('address', $result);
        $this->assertArrayHasKey('children', $result);
        $this->assertCount(2, $result['children']);
    }

    public function test_returns_null_with_children_when_not_found(): void
    {
        $this->repository->expects($this->once())
            ->method('findByIdWithChildren')
            ->with('non-existent')
            ->willReturn(null);

        $result = $this->useCase->execute('non-existent', withChildren: true);

        $this->assertNull($result);
    }
}
