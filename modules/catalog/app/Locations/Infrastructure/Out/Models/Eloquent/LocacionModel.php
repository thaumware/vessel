<?php

namespace App\Locations\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocacionModel extends EloquentModel
{
    use SoftDeletes, HasFactory;

    protected $table = 'locaciones_locations';

    protected $fillable = [
        'id',
        'name',
        'description',
        'type',
        'address_id',
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

    protected $casts = [
        'type' => 'string',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(AddressModel::class, 'address_id');
    }
}