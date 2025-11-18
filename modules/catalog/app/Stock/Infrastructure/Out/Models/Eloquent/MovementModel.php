<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;

class MovementModel extends EloquentModel
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'id',
        'movement_id',
        'sku',
        'location_from_id',
        'location_from_type',
        'location_to_id',
        'location_to_type',
        'quantity',
        'balance_after',
        'movement_type',
        'reference',
        'user_id',
        'workspace_id',
        'meta',
        'processed_at',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
