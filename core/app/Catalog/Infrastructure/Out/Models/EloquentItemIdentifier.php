<?php

namespace App\Catalog\Infrastructure\Out\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentItemIdentifier extends Model
{
    protected $table = 'catalog_item_identifiers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'item_id',
        'variant_id',
        'type',
        'value',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
