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
        'slug',
        'description',
        'vocabulary_id',
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

    public function vocabulary()
    {
        return $this->belongsTo(VocabularyModel::class, 'vocabulary_id', 'id');
    }

    /**
     * Get parent relations (relations where this term is the child)
     */
    public function parentRelations()
    {
        return $this->hasMany(TermRelationModel::class, 'from_term_id', 'id')
            ->where('relation_type', 'parent');
    }

    /**
     * Get child relations (relations where this term is the parent)
     */
    public function childRelations()
    {
        return $this->hasMany(TermRelationModel::class, 'to_term_id', 'id')
            ->where('relation_type', 'parent');
    }

    /**
     * Get all relations involving this term
     */
    public function relations()
    {
        return $this->hasMany(TermRelationModel::class, 'from_term_id', 'id');
    }

    /**
     * Items tagged with this term
     */
    public function items()
    {
        return $this->belongsToMany(
            \App\Catalog\Infrastructure\Out\Models\EloquentItem::class,
            'catalog_item_terms',
            'term_id',
            'item_id'
        );
    }
}