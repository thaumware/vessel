<?php 

namespace App\Locations\Infrastructure\Out\Models;

use App\Shared\Adapters\Eloquent\EloquentModel;

class EloquentCity extends EloquentAddress
{
    protected static function booted()
    {
        static::addGlobalScope('type', function ($query) {
            $query->where('type', 'city');
        });
    }
}