<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;

class TermRelationshipModel extends EloquentModel
{
    protected $table = 'catalog_term_relationships';

    protected $fillable = [
        'term_id',

        'entity_id',
        'entity_type',


        'created_at',
        'updated_at',
    ];
}