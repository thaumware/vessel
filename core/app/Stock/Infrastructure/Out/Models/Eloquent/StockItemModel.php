<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItemModel extends Model
{
    use SoftDeletes;

    protected $table = 'stock_items';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'sku',
        'catalog_item_id',
        'catalog_origin',
        'location_id',
        'location_type',
        'status_id',
        'item_type',
        'item_id',
        'quantity',
        'reserved_quantity',
        'lot_number',
        'expiration_date',
        'serial_number',
        'workspace_id',
        'meta',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'expiration_date' => 'date',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
