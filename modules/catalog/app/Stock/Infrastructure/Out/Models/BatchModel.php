<?php

namespace App\Catalog\Stock\Infrastructure\Out\Models;

use App\Shared\Adapters\Eloquent\EloquentModel;

class BatchModel extends EloquentModel
{
    protected $table = 'batches';

    protected $fillable = [
        'code',
        'product_id',
        'quantity',
        'expiration_date',
        'created_at',
        'updated_at',
    ];
}