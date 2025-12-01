<?php

namespace App\Locations\Infrastructure\Out\Models\Eloquent;

use App\Locations\Domain\Entities\Address;
use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\ValueObjects\AddressType;

class EloquentAddressRepository implements AddressRepository
{
    public function save(Address $address): void
    {
        $addressModel = AddressModel::find($address->getId()) ?? new AddressModel();

        $addressModel->id = $address->getId();
        $addressModel->name = $address->getName();
        $addressModel->description = $address->getDescription();
        $addressModel->address_type = $address->getAddressType();
        $addressModel->parent_address_id = $address->getParentAddressId();

        $addressModel->save();
    }

    public function findById(string $id): ?Address
    {
        $addressModel = AddressModel::find($id);

        if (!$addressModel) {
            return null;
        }

        return $this->toDomain($addressModel);
    }

    public function findAll(): array
    {
        return AddressModel::all()->map(fn($m) => $this->toDomain($m))->toArray();
    }

    public function findByIdWithChildren(string $id): ?array
    {
        $address = $this->findById($id);

        if (!$address) {
            return null;
        }

        $children = $this->findByParentId($id);

        return [
            'address' => $address,
            'children' => $children,
        ];
    }

    public function findByParentId(string $parentId): array
    {
        return AddressModel::where('parent_address_id', $parentId)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function update(Address $address): void
    {
        $this->save($address);
    }

    public function delete(Address $address): void
    {
        $addressModel = AddressModel::find($address->getId());

        if ($addressModel) {
            $addressModel->delete();
        }
    }

    private function toDomain(AddressModel $model): Address
    {
        return new Address(
            id: $model->id,
            name: $model->name,
            addressType: AddressType::tryFrom($model->address_type) ?? AddressType::STREET,
            parentAddressId: $model->parent_address_id,
            description: $model->description,
        );
    }
}
