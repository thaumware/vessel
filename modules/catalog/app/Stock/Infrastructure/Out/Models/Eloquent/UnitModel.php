<?php

namespace App\Stock\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;

class UnitModel extends EloquentModel
{
    protected $table = 'stock_units';

    protected $fillable = [
        'id',
        'code',
        'name',
        'workspace_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
