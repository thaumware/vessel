<?php

namespace App\Shared\Adapters\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletes;

// Con uuid 
class EloquentModel extends \Illuminate\Database\Eloquent\Model
{
    use SoftDeletes;
    public $primaryKey = 'id';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

}