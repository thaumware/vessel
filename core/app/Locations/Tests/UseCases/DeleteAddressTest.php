<?php

namespace App\Locations\Tests\UseCases;

use App\Locations\Application\UseCases\DeleteAddress;
use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;
use PHPUnit\Framework\TestCase;

class DeleteAddressTest extends TestCase
{
    /** @var AddressRepository&\PHPUnit\Framework\MockObject\MockObject */
    private $repository;
    private DeleteAddress $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AddressRepository::class);
        $this->useCase = new DeleteAddress($this->repository);
    }

    public function test_returns_false_when_address_not_found(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with('non-existent')
            ->willReturn(null);

        $result = $this->useCase->execute('non-existent');

        $this->assertFalse($result);
    }

    public function test_deletes_address_and_returns_true(): void
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

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($address);

        $result = $this->useCase->execute('test-uuid');

        $this->assertTrue($result);
    }
}
