<?php

declare(strict_types=1);

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockLocationSettingsModel extends Model
{
    use HasUuids;

    protected $table = 'stock_location_settings';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'location_id',
        'max_quantity',
        'max_weight',
        'max_volume',
        'allowed_item_types',
        'allow_mixed_lots',
        'allow_mixed_skus',
        'fifo_enforced',
        'is_active',
        'workspace_id',
        'meta',
    ];

    protected $casts = [
        'max_quantity' => 'integer',
        'max_weight' => 'float',
        'max_volume' => 'float',
        'allowed_item_types' => 'array',
        'allow_mixed_lots' => 'boolean',
        'allow_mixed_skus' => 'boolean',
        'fifo_enforced' => 'boolean',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];
}
