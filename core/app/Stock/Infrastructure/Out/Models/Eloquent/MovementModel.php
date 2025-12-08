<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovementModel extends Model
{
    use SoftDeletes;

    protected $table = 'stock_movements';

    protected $fillable = [
        'id',
        'sku',
        'movement_type',
        'status',
        'location_from_id',
        'location_to_id',
        'quantity',
        'balance_after',
        'reference',
        'user_id',
        'workspace_id',
        'meta',
        'processed_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'processed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
