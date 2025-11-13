<?php

namespace App\Locations\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddressModel extends EloquentModel
{
    use SoftDeletes, HasFactory;

    protected $table = 'locations_addresses';

    protected $fillable = [
        'id',
        'name',
        'address_type',
        'description',
        'workspace_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}