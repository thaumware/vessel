<?php

namespace App\Shared\Adapters\Eloquent;

// Con uuid 
class EloquentModel extends \Illuminate\Database\Eloquent\Model
{
    public $primaryKey = 'id';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

}