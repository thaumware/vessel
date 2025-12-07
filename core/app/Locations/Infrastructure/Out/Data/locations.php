<?php

/**
 * Datos de ejemplo para Locations
 * 
 * Jerarquía:
 *   - Warehouses/Stores (parent_id: null) - ubicaciones raíz
 *   - Storage Units (parent_id: xxx) - hijos de warehouses/stores
 */
return [
    // === UBICACIONES RAÍZ ===
    [
        'id' => 'loc-camptech',
        'name' => 'Camptech',
        'address_id' => 'addr-1',
        'type' => 'warehouse',
        'description' => 'Bodega principal Camptech',
        'parent_id' => null,
    ],
    [
        'id' => 'loc-fablab',
        'name' => 'Laboratorio FabLab',
        'address_id' => 'addr-2',
        'type' => 'warehouse',
        'description' => 'Laboratorio de fabricación',
        'parent_id' => null,
    ],
    [
        'id' => 'loc-oficina',
        'name' => 'Oficina Central',
        'address_id' => 'addr-3',
        'type' => 'office',
        'description' => 'Oficina administrativa',
        'parent_id' => null,
    ],
    
    // === STORAGE UNITS DE CAMPTECH ===
    [
        'id' => 'loc-camptech-estante-a',
        'name' => 'Estante A',
        'address_id' => 'addr-1',
        'type' => 'storage_unit',
        'description' => 'Estante para papelería',
        'parent_id' => 'loc-camptech',
    ],
    [
        'id' => 'loc-camptech-estante-b',
        'name' => 'Estante B',
        'address_id' => 'addr-1',
        'type' => 'storage_unit',
        'description' => 'Estante para electrónicos',
        'parent_id' => 'loc-camptech',
    ],
    [
        'id' => 'loc-camptech-cajon-1',
        'name' => 'Cajón 1',
        'address_id' => 'addr-1',
        'type' => 'storage_unit',
        'description' => 'Cajón pequeños',
        'parent_id' => 'loc-camptech',
    ],
    
    // === STORAGE UNITS DE FABLAB ===
    [
        'id' => 'loc-fablab-mesa-1',
        'name' => 'Mesa de trabajo 1',
        'address_id' => 'addr-2',
        'type' => 'storage_unit',
        'description' => 'Mesa principal',
        'parent_id' => 'loc-fablab',
    ],
    [
        'id' => 'loc-fablab-rack-herramientas',
        'name' => 'Rack de herramientas',
        'address_id' => 'addr-2',
        'type' => 'storage_unit',
        'description' => 'Organizador de herramientas',
        'parent_id' => 'loc-fablab',
    ],
];