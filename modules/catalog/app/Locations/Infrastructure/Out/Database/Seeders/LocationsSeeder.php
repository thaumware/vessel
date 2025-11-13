<?php

namespace App\Locations\Infrastructure\Out\Database\Seeders;

use App\Locations\Infrastructure\Out\Models\Eloquent\CityModel;
use App\Locations\Infrastructure\Out\Models\Eloquent\AddressModel;
use App\Locations\Infrastructure\Out\Models\Eloquent\LocationModel;
use App\Locations\Infrastructure\Out\Models\Eloquent\WarehouseModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationsSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample cities
        $madrid = CityModel::create([
            'id' => Str::uuid(),
            'name' => 'Madrid',
            'state' => 'Madrid',
            'country' => 'Spain'
        ]);

        $barcelona = CityModel::create([
            'id' => Str::uuid(),
            'name' => 'Barcelona',
            'state' => 'Cataluña',
            'country' => 'Spain'
        ]);

        // Create sample addresses
        $address1 = AddressModel::create([
            'id' => Str::uuid(),
            'street' => 'Calle Gran Vía, 1',
            'postal_code' => '28001',
            'city_id' => $madrid->id
        ]);

        $address2 = AddressModel::create([
            'id' => Str::uuid(),
            'street' => 'Paseo de Gracia, 10',
            'postal_code' => '08001',
            'city_id' => $barcelona->id
        ]);

        $address3 = AddressModel::create([
            'id' => Str::uuid(),
            'street' => 'Plaza Mayor, 5',
            'postal_code' => '28012',
            'city_id' => $madrid->id
        ]);

        // Create sample locations
        $warehouse = LocationModel::create([
            'id' => Str::uuid(),
            'name' => 'Almacén Central Madrid',
            'description' => 'Almacén principal con capacidad para 2000 pallets',
            'type' => 'warehouse',
            'address_id' => $address1->id
        ]);

        $store = LocationModel::create([
            'id' => Str::uuid(),
            'name' => 'Tienda Barcelona Centro',
            'description' => 'Tienda flagship en el centro de Barcelona',
            'type' => 'store',
            'address_id' => $address2->id
        ]);

        $office = LocationModel::create([
            'id' => Str::uuid(),
            'name' => 'Oficina Corporativa',
            'description' => 'Sede central de la empresa',
            'type' => 'office',
            'address_id' => $address3->id
        ]);

        // Create warehouse details
        WarehouseModel::create([
            'id' => Str::uuid(),
            'location_id' => $warehouse->id,
            'capacity' => 2000,
            'temperature_controlled' => true
        ]);
    }
}