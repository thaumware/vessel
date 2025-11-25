<?php

namespace App\Locations\Domain\Interfaces;

use App\Locations\Domain\Entities\Address;

interface AddressRepository
{
    public function findAll(): array;

    public function findById(string $id): ?Address;

    public function findByIdWithChildren(string $id): ?array;

    public function findByParentId(string $parentId): array;

    public function save(Address $address): void;

    public function update(Address $address): void;

    public function delete(Address $address): void;
}
