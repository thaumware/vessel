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
        'user_id', // DEPRECATED: usar created_by_id
        'workspace_id',
        'meta',
        'created_by_id',
        'created_by_type',
        'processed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Constantes de validación según schema
    public const MAX_MOVEMENT_TYPE_LENGTH = 64;
    public const MAX_STATUS_LENGTH = 32;
    public const MAX_REFERENCE_LENGTH = 255;

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'processed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
