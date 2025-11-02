<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use App\Shared\Adapters\Eloquent\EloquentModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermModel extends EloquentModel
{
    use SoftDeletes, HasFactory;

    protected $table = 'catalog_terms';

    protected $fillable = [
        'id',
        'name',
        'vocabulary_id',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function vocabulary()
    {
        return $this->belongsTo(VocabularyModel::class, 'vocabulary_id', 'id');
    }
}