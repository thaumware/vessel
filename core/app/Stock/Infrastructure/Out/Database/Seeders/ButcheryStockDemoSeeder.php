<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Out\Database\Seeders;

use App\Catalog\Infrastructure\Out\Database\Seeders\ButcheryCatalogSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ButcheryStockDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            'units' => [
                ['id' => '60000000-0000-4000-8000-000000000001', 'code' => 'kg', 'name' => 'Kilogramo'],
                ['id' => '60000000-0000-4000-8000-000000000003', 'code' => 'g', 'name' => 'Gramo'],
                ['id' => '60000000-0000-4000-8000-000000000002', 'code' => 'unit', 'name' => 'Unidad'],
            ],
            'statuses' => [
                ['id' => '61000000-0000-4000-8000-000000000001', 'code' => 'available', 'label' => 'Disponible', 'description' => 'Stock disponible', 'sort_order' => 10],
                ['id' => '61000000-0000-4000-8000-000000000002', 'code' => 'reserved', 'label' => 'Reservado', 'description' => 'Stock comprometido', 'sort_order' => 20],
                ['id' => '61000000-0000-4000-8000-000000000003', 'code' => 'quarantine', 'label' => 'Cuarentena', 'description' => 'Stock en revision', 'sort_order' => 30],
            ],
            'locationTypes' => [
                ['id' => '62000000-0000-4000-8000-000000000001', 'code' => 'store', 'label' => 'Tienda'],
                ['id' => '62000000-0000-4000-8000-000000000002', 'code' => 'cold_room', 'label' => 'Camara Fria'],
                ['id' => '62000000-0000-4000-8000-000000000003', 'code' => 'display', 'label' => 'Mostrador'],
                ['id' => '62000000-0000-4000-8000-000000000004', 'code' => 'freezer', 'label' => 'Freezer'],
                ['id' => '62000000-0000-4000-8000-000000000005', 'code' => 'process_room', 'label' => 'Sala de Proceso'],
            ],
            'locations' => [
                'central' => ['id' => '62100000-0000-4000-8000-000000000001', 'name' => 'Carniceria Central', 'type' => 'store', 'level' => 0, 'path' => '/62100000-0000-4000-8000-000000000001', 'parent' => null],
                'cold' => ['id' => '62100000-0000-4000-8000-000000000002', 'name' => 'Camara Fria', 'type' => 'cold_room', 'level' => 1, 'path' => '/62100000-0000-4000-8000-000000000001/62100000-0000-4000-8000-000000000002', 'parent' => 'central'],
                'display' => ['id' => '62100000-0000-4000-8000-000000000003', 'name' => 'Mostrador', 'type' => 'display', 'level' => 1, 'path' => '/62100000-0000-4000-8000-000000000001/62100000-0000-4000-8000-000000000003', 'parent' => 'central'],
                'freezer' => ['id' => '62100000-0000-4000-8000-000000000004', 'name' => 'Freezer', 'type' => 'freezer', 'level' => 1, 'path' => '/62100000-0000-4000-8000-000000000001/62100000-0000-4000-8000-000000000004', 'parent' => 'central'],
                'process' => ['id' => '62100000-0000-4000-8000-000000000005', 'name' => 'Sala de Proceso', 'type' => 'process_room', 'level' => 1, 'path' => '/62100000-0000-4000-8000-000000000001/62100000-0000-4000-8000-000000000005', 'parent' => 'central'],
            ],
            'settings' => [
                'cold' => ['id' => '62200000-0000-4000-8000-000000000001', 'max_quantity' => 1200, 'max_weight' => 1200, 'max_reservation_percentage' => 60, 'fifo_enforced' => true, 'temperature' => '0-4C'],
                'display' => ['id' => '62200000-0000-4000-8000-000000000002', 'max_quantity' => 250, 'max_weight' => 250, 'max_reservation_percentage' => 40, 'fifo_enforced' => false, 'temperature' => '0-6C'],
                'freezer' => ['id' => '62200000-0000-4000-8000-000000000003', 'max_quantity' => 900, 'max_weight' => 900, 'max_reservation_percentage' => 70, 'fifo_enforced' => true, 'temperature' => '-18C'],
                'process' => ['id' => '62200000-0000-4000-8000-000000000004', 'max_quantity' => 400, 'max_weight' => 400, 'max_reservation_percentage' => 20, 'fifo_enforced' => false, 'temperature' => '8-12C'],
            ],
            'lots' => [
                ['id' => '62300000-0000-4000-8000-000000000001', 'number' => 'LOT-CER-COSTI-001', 'sku' => 'CER-COSTI', 'status' => 'active', 'production' => 8, 'reception' => 6, 'expiration' => 18],
                ['id' => '62300000-0000-4000-8000-000000000002', 'number' => 'LOT-CER-LONGA-001', 'sku' => 'CER-LONGA', 'status' => 'active', 'production' => 2, 'reception' => 1, 'expiration' => 25],
                ['id' => '62300000-0000-4000-8000-000000000003', 'number' => 'LOT-CER-EMBU-001', 'sku' => 'CER-EMBU', 'status' => 'active', 'production' => 6, 'reception' => 4, 'expiration' => 12],
                ['id' => '62300000-0000-4000-8000-000000000004', 'number' => 'LOT-POL-ENTERO-001', 'sku' => 'POL-ENTERO', 'status' => 'active', 'production' => 3, 'reception' => 2, 'expiration' => 8],
                ['id' => '62300000-0000-4000-8000-000000000005', 'number' => 'LOT-POL-PECHU-001', 'sku' => 'POL-PECHU', 'status' => 'active', 'production' => 3, 'reception' => 2, 'expiration' => 7],
                ['id' => '62300000-0000-4000-8000-000000000006', 'number' => 'LOT-POL-QA-001', 'sku' => 'POL-PECHU-QA', 'status' => 'quarantine', 'production' => 4, 'reception' => 4, 'expiration' => 2],
                ['id' => '62300000-0000-4000-8000-000000000007', 'number' => 'LOT-VAC-LOMO-001', 'sku' => 'VAC-LOMO', 'status' => 'active', 'production' => 10, 'reception' => 7, 'expiration' => 14],
                ['id' => '62300000-0000-4000-8000-000000000008', 'number' => 'LOT-VAC-ASADO-001', 'sku' => 'VAC-ASADO', 'status' => 'active', 'production' => 9, 'reception' => 6, 'expiration' => 13],
                ['id' => '62300000-0000-4000-8000-000000000009', 'number' => 'LOT-VAC-BURG-001', 'sku' => 'VAC-BURG', 'status' => 'active', 'production' => 2, 'reception' => 1, 'expiration' => 30],
            ],
            'stock' => [
                ['id' => '62400000-0000-4000-8000-000000000001', 'sku' => 'CER-COSTI', 'name' => 'Costillar de Cerdo', 'location' => 'cold', 'location_type' => 'cold_room', 'qty' => 22, 'reserved' => 0, 'lot' => 'LOT-CER-COSTI-001', 'status' => 'available'],
                ['id' => '62400000-0000-4000-8000-000000000002', 'sku' => 'CER-LONGA', 'name' => 'Longaniza de Cerdo', 'location' => 'freezer', 'location_type' => 'freezer', 'qty' => 18, 'reserved' => 3, 'lot' => 'LOT-CER-LONGA-001', 'status' => 'available'],
                ['id' => '62400000-0000-4000-8000-000000000003', 'sku' => 'CER-EMBU', 'name' => 'Carne para Embutidos de Cerdo', 'location' => 'process', 'location_type' => 'process_room', 'qty' => 28, 'reserved' => 0, 'lot' => 'LOT-CER-EMBU-001', 'status' => 'available'],
                ['id' => '62400000-0000-4000-8000-000000000004', 'sku' => 'POL-ENTERO', 'name' => 'Pollo Entero', 'location' => 'cold', 'location_type' => 'cold_room', 'qty' => 36, 'reserved' => 4, 'lot' => 'LOT-POL-ENTERO-001', 'status' => 'available'],
                ['id' => '62400000-0000-4000-8000-000000000005', 'sku' => 'POL-PECHU', 'name' => 'Pechuga de Pollo', 'location' => 'display', 'location_type' => 'display', 'qty' => 16, 'reserved' => 0, 'lot' => 'LOT-POL-PECHU-001', 'status' => 'available'],
                ['id' => '62400000-0000-4000-8000-000000000006', 'sku' => 'POL-PECHU-QA', 'name' => 'Pechuga con Hueso de Pollo', 'location' => 'freezer', 'location_type' => 'freezer', 'qty' => 6, 'reserved' => 0, 'lot' => 'LOT-POL-QA-001', 'status' => 'quarantine'],
                ['id' => '62400000-0000-4000-8000-000000000007', 'sku' => 'VAC-LOMO', 'name' => 'Lomo Vetado de Vacuno', 'location' => 'cold', 'location_type' => 'cold_room', 'qty' => 28, 'reserved' => 3, 'lot' => 'LOT-VAC-LOMO-001', 'status' => 'available'],
                ['id' => '62400000-0000-4000-8000-000000000008', 'sku' => 'VAC-ASADO', 'name' => 'Asado de Tira de Vacuno', 'location' => 'display', 'location_type' => 'display', 'qty' => 12, 'reserved' => 0, 'lot' => 'LOT-VAC-ASADO-001', 'status' => 'available'],
                ['id' => '62400000-0000-4000-8000-000000000009', 'sku' => 'VAC-BURG', 'name' => 'Hamburguesa de Vacuno', 'location' => 'freezer', 'location_type' => 'freezer', 'qty' => 32, 'reserved' => 6, 'lot' => 'LOT-VAC-BURG-001', 'status' => 'available'],
            ],
            'reservations' => [
                ['id' => '62500000-0000-4000-8000-000000000001', 'stock_id' => '62400000-0000-4000-8000-000000000002', 'location' => 'freezer', 'qty' => 3, 'reference' => 'SO-DEMO-1001'],
                ['id' => '62500000-0000-4000-8000-000000000002', 'stock_id' => '62400000-0000-4000-8000-000000000004', 'location' => 'cold', 'qty' => 4, 'reference' => 'SO-DEMO-1002'],
                ['id' => '62500000-0000-4000-8000-000000000003', 'stock_id' => '62400000-0000-4000-8000-000000000007', 'location' => 'cold', 'qty' => 3, 'reference' => 'SO-DEMO-1003'],
                ['id' => '62500000-0000-4000-8000-000000000004', 'stock_id' => '62400000-0000-4000-8000-000000000009', 'location' => 'freezer', 'qty' => 6, 'reference' => 'SO-DEMO-1004'],
            ],
            'movements' => [
                ['id' => '62600000-0000-4000-8000-000000000001', 'sku' => 'CER-COSTI', 'type' => 'receipt', 'to' => 'cold', 'qty' => 22, 'balance' => 22, 'reference' => 'PO-DEMO-CER-001', 'status' => 'available'],
                ['id' => '62600000-0000-4000-8000-000000000002', 'sku' => 'CER-LONGA', 'type' => 'production', 'from' => 'process', 'to' => 'freezer', 'qty' => 18, 'balance' => 18, 'reference' => 'PROD-DEMO-CER-001', 'status' => 'available'],
                ['id' => '62600000-0000-4000-8000-000000000003', 'sku' => 'CER-EMBU', 'type' => 'receipt', 'to' => 'process', 'qty' => 28, 'balance' => 28, 'reference' => 'PO-DEMO-CER-003', 'status' => 'available'],
                ['id' => '62600000-0000-4000-8000-000000000004', 'sku' => 'POL-ENTERO', 'type' => 'receipt', 'to' => 'cold', 'qty' => 36, 'balance' => 36, 'reference' => 'PO-DEMO-POL-001', 'status' => 'available'],
                ['id' => '62600000-0000-4000-8000-000000000005', 'sku' => 'POL-PECHU', 'type' => 'receipt', 'to' => 'display', 'qty' => 16, 'balance' => 16, 'reference' => 'PO-DEMO-POL-002', 'status' => 'available'],
                ['id' => '62600000-0000-4000-8000-000000000006', 'sku' => 'POL-PECHU-QA', 'type' => 'receipt', 'to' => 'freezer', 'qty' => 6, 'balance' => 6, 'reference' => 'QA-DEMO-POL-001', 'status' => 'quarantine'],
                ['id' => '62600000-0000-4000-8000-000000000007', 'sku' => 'VAC-LOMO', 'type' => 'receipt', 'to' => 'cold', 'qty' => 28, 'balance' => 28, 'reference' => 'PO-DEMO-VAC-001', 'status' => 'available'],
                ['id' => '62600000-0000-4000-8000-000000000008', 'sku' => 'VAC-ASADO', 'type' => 'receipt', 'to' => 'display', 'qty' => 12, 'balance' => 12, 'reference' => 'PO-DEMO-VAC-002', 'status' => 'available'],
                ['id' => '62600000-0000-4000-8000-000000000009', 'sku' => 'VAC-BURG', 'type' => 'production', 'from' => 'process', 'to' => 'freezer', 'qty' => 32, 'balance' => 32, 'reference' => 'PROD-DEMO-VAC-001', 'status' => 'available'],
            ],
        ];

        DB::transaction(function () use ($data, $now): void {
            $this->call(ButcheryCatalogSeeder::class);

            $catalogIds = DB::table('catalog_items')
                ->whereIn('name', array_column($data['stock'], 'name'))
                ->pluck('id', 'name')
                ->toArray();

            if (count($catalogIds) !== count($data['stock'])) {
                throw new RuntimeException('Faltan items del catalogo para cargar el stock demo.');
            }

            foreach ($data['units'] as $unit) {
                DB::table('stock_units')->updateOrInsert(
                    ['code' => $unit['code']],
                    ['id' => $unit['id'], 'name' => $unit['name'], 'workspace_id' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null]
                );
            }

            $statusIds = [];
            foreach ($data['statuses'] as $status) {
                DB::table('stock_statuses')->updateOrInsert(
                    ['workspace_id' => null, 'code' => $status['code']],
                    ['id' => $status['id'], 'label' => $status['label'], 'description' => $status['description'], 'is_active' => true, 'sort_order' => $status['sort_order'], 'created_at' => $now, 'updated_at' => $now]
                );
                $statusIds[$status['code']] = $status['id'];
            }

            $locationTypeIds = [];
            foreach ($data['locationTypes'] as $type) {
                DB::table('location_types')->updateOrInsert(
                    ['workspace_id' => null, 'code' => $type['code']],
                    ['id' => $type['id'], 'label' => $type['label'], 'description' => $type['label'] . ' demo', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
                $locationTypeIds[$type['code']] = $type['id'];
            }

            foreach ($data['locations'] as $key => $location) {
                DB::table('locations_locations')->updateOrInsert(
                    ['id' => $location['id']],
                    [
                        'name' => $location['name'],
                        'description' => $location['name'] . ' demo',
                        'type' => $location['type'],
                        'address_id' => null,
                        'parent_id' => $location['parent'] !== null ? $data['locations'][$location['parent']]['id'] : null,
                        'level' => $location['level'],
                        'path' => $location['path'],
                        'workspace_id' => null,
                        'type_id' => $locationTypeIds[$location['type']],
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]
                );
            }

            foreach ($data['settings'] as $locationKey => $setting) {
                DB::table('stock_location_settings')->updateOrInsert(
                    ['location_id' => $data['locations'][$locationKey]['id']],
                    [
                        'id' => $setting['id'],
                        'max_quantity' => $setting['max_quantity'],
                        'storage_uom_id' => $data['units'][0]['id'],
                        'max_weight' => $setting['max_weight'],
                        'max_volume' => null,
                        'allowed_item_types' => json_encode(['unit']),
                        'allow_mixed_lots' => true,
                        'allow_mixed_skus' => true,
                        'allow_negative_stock' => false,
                        'max_reservation_percentage' => $setting['max_reservation_percentage'],
                        'fifo_enforced' => $setting['fifo_enforced'],
                        'is_active' => true,
                        'workspace_id' => null,
                        'meta' => json_encode(['temperature' => $setting['temperature']]),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $lotExpirations = [];
            foreach ($data['lots'] as $lot) {
                $expiration = $now->copy()->addDays($lot['expiration'])->toDateString();
                $lotExpirations[$lot['number']] = $expiration;

                DB::table('stock_lots')->updateOrInsert(
                    ['lot_number' => $lot['number']],
                    [
                        'id' => $lot['id'],
                        'sku' => $lot['sku'],
                        'expiration_date' => $expiration,
                        'production_date' => $now->copy()->subDays($lot['production'])->toDateString(),
                        'reception_date' => $now->copy()->subDays($lot['reception'])->toDateString(),
                        'supplier_id' => null,
                        'supplier_lot_number' => $lot['number'],
                        'status' => $lot['status'],
                        'workspace_id' => null,
                        'meta' => json_encode(['demo' => true]),
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]
                );
            }

            foreach ($data['stock'] as $item) {
                DB::table('stock_items')->updateOrInsert(
                    ['sku' => $item['sku'], 'location_id' => $data['locations'][$item['location']]['id']],
                    [
                        'id' => $item['id'],
                        'catalog_item_id' => $catalogIds[$item['name']],
                        'catalog_origin' => 'internal_catalog',
                        'location_type' => $item['location_type'],
                        'quantity' => $item['qty'],
                        'reserved_quantity' => $item['reserved'],
                        'status_id' => $statusIds[$item['status']],
                        'item_type' => 'unit',
                        'item_id' => $catalogIds[$item['name']],
                        'lot_number' => $item['lot'],
                        'expiration_date' => $lotExpirations[$item['lot']],
                        'workspace_id' => null,
                        'meta' => json_encode(['demo' => true]),
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]
                );

                DB::table('stock_current')->updateOrInsert(
                    ['sku' => $item['sku'], 'location_id' => $data['locations'][$item['location']]['id'], 'location_type' => $item['location_type']],
                    [
                        'id' => $item['id'],
                        'quantity' => $item['qty'],
                        'workspace_id' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]
                );
            }

            foreach ($data['reservations'] as $reservation) {
                DB::table('stock_reservations')->updateOrInsert(
                    ['id' => $reservation['id']],
                    [
                        'item_id' => $reservation['stock_id'],
                        'location_id' => $data['locations'][$reservation['location']]['id'],
                        'quantity' => $reservation['qty'],
                        'reserved_by' => 'demo-seeder',
                        'reference_type' => 'sales_order',
                        'reference_id' => $reservation['reference'],
                        'status' => 'active',
                        'expires_at' => $now->copy()->addDays(2),
                        'created_at' => $now,
                        'released_at' => null,
                    ]
                );
            }

            foreach ($data['movements'] as $movement) {
                DB::table('stock_movements')->updateOrInsert(
                    ['id' => $movement['id']],
                    [
                        'sku' => $movement['sku'],
                        'movement_type' => $movement['type'],
                        'status' => 'processed',
                        'location_from_id' => isset($movement['from']) ? $data['locations'][$movement['from']]['id'] : null,
                        'location_to_id' => isset($movement['to']) ? $data['locations'][$movement['to']]['id'] : null,
                        'quantity' => $movement['qty'],
                        'balance_after' => $movement['balance'],
                        'reference' => $movement['reference'],
                        'workspace_id' => null,
                        'meta' => json_encode(['demo' => true]),
                        'created_at' => $now,
                        'updated_at' => $now,
                        'processed_at' => $now,
                        'deleted_at' => null,
                        'resulting_status_id' => $statusIds[$movement['status']],
                    ]
                );
            }
        });

        $this->command?->info('Butchery stock demo cargado.');
    }
}
