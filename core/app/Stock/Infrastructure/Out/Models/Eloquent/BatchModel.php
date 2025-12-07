<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;

class BatchModel extends EloquentModel
{
    protected $table = 'stock_batches';

    protected $fillable = [
        'id',
        'sku',
        'location_id',
        'quantity',
        'lot_number',
        'workspace_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
