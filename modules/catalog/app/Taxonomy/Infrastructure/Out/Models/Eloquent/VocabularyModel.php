<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;

class VocabularyModel extends EloquentModel
{
    protected $table = 'taxonomy_vocabularies';

    protected $fillable = [
        'id',
        'name',
        'description',
        'slug',
        'workspace_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}