<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;

class StockModel extends EloquentModel
{
    protected $table = 'stock_current';

    protected $fillable = [
        'id',
        'sku',
        'location_id',
        'location_type',
        'quantity',
        'workspace_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
