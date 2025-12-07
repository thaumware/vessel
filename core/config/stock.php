<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stock Statuses (Estados de Stock Personalizables)
    |--------------------------------------------------------------------------
    |
    | Define estados de stock con reglas de comportamiento.
    | Cada estado puede tener reglas que controlen:
    | - allow_movements: permite movimientos de salida
    | - allow_reservations: permite reservar desde este estado
    | - blocks_availability: bloquea disponibilidad (no cuenta como stock disponible)
    | - requires_approval: movimientos requieren aprobación
    | - allowed_transitions: array de estados a los que puede transicionar
    | - auto_transition: transición automática bajo condiciones
    |
    */

    'statuses' => [
        'available' => [
            'label' => 'Disponible',
            'rules' => [
                'allow_movements' => true,
                'allow_reservations' => true,
                'blocks_availability' => false,
                'requires_approval' => false,
            ],
        ],

        'reserved' => [
            'label' => 'Reservado',
            'rules' => [
                'allow_movements' => false, // No se puede mover hasta liberar reserva
                'allow_reservations' => false,
                'blocks_availability' => true, // No cuenta como disponible
                'requires_approval' => false,
                'allowed_transitions' => ['available', 'in_transit', 'damaged', 'lost'],
            ],
        ],

        'in_transit' => [
            'label' => 'En tránsito',
            'rules' => [
                'allow_movements' => false, // Bloqueado mientras está en movimiento
                'allow_reservations' => false,
                'blocks_availability' => true,
                'requires_approval' => false,
                'allowed_transitions' => ['available', 'damaged', 'lost'],
                'auto_transition' => [
                    'to' => 'available',
                    'condition' => 'on_movement_complete', // Cuando se completa el movimiento
                ],
            ],
        ],

        'on_hold' => [
            'label' => 'En espera',
            'rules' => [
                'allow_movements' => false, // Requiere movimiento positivo primero
                'allow_reservations' => false,
                'blocks_availability' => true,
                'requires_approval' => true, // Necesita aprobación para mover
                'allowed_transitions' => ['available', 'quarantine', 'damaged'],
            ],
        ],

        'quarantine' => [
            'label' => 'Cuarentena',
            'rules' => [
                'allow_movements' => false,
                'allow_reservations' => false,
                'blocks_availability' => true,
                'requires_approval' => true,
                'allowed_transitions' => ['available', 'damaged', 'disposed'],
            ],
        ],

        'damaged' => [
            'label' => 'Dañado',
            'rules' => [
                'allow_movements' => false,
                'allow_reservations' => false,
                'blocks_availability' => true,
                'requires_approval' => true,
                'allowed_transitions' => ['disposed', 'available'], // Disponible si se repara
            ],
        ],

        'lost' => [
            'label' => 'Perdido',
            'rules' => [
                'allow_movements' => false,
                'allow_reservations' => false,
                'blocks_availability' => true,
                'requires_approval' => false,
                'allowed_transitions' => ['disposed', 'available'], // Si se encuentra
            ],
        ],

        'disposed' => [
            'label' => 'Descartado',
            'rules' => [
                'allow_movements' => false,
                'allow_reservations' => false,
                'blocks_availability' => true,
                'requires_approval' => false,
                'allowed_transitions' => [], // Terminal, no puede transicionar
            ],
        ],

        'expired' => [
            'label' => 'Vencido',
            'rules' => [
                'allow_movements' => false,
                'allow_reservations' => false,
                'blocks_availability' => true,
                'requires_approval' => true,
                'allowed_transitions' => ['disposed'],
                'auto_transition' => [
                    'to' => 'expired',
                    'condition' => 'lot_expired', // Automático cuando vence el lote
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Status
    |--------------------------------------------------------------------------
    |
    | Estado por defecto para nuevo stock
    |
    */

    'default_status' => 'available',

    /*
    |--------------------------------------------------------------------------
    | Location Rules
    |--------------------------------------------------------------------------
    |
    | Reglas configurables por ubicación (se pueden override en la BD)
    |
    */

    'location_rules' => [
        // Regla: ubicaciones de tipo "staging" auto-transicionan a "available"
        'auto_release_from_staging' => [
            'enabled' => true,
            'after_hours' => 24,
            'from_status' => 'in_transit',
            'to_status' => 'available',
        ],

        // Regla: ubicaciones de cuarentena bloquean movimientos
        'quarantine_locations' => [
            'enabled' => true,
            'location_types' => ['quarantine'],
            'enforce_status' => 'quarantine',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Reglas de validación aplicables a movimientos
    |
    */

    'validation_rules' => [
        // Regla: no permitir movimientos si el estado lo bloquea
        'check_status_allows_movement' => true,

        // Regla: validar transiciones permitidas entre estados
        'validate_status_transitions' => true,

        // Regla: requerir aprobación si el estado lo demanda
        'enforce_approval_requirement' => true,
    ],
];
