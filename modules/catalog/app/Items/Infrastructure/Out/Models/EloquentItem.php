<?php

namespace App\Items\Infrastructure\Out\Models;

use App\Taxonomy\Infrastructure\Out\Models\Eloquent\TermModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentItem extends Model
{
    use SoftDeletes;

    protected $table = 'catalog_items';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'uom_id',
        'notes',
        'status',
        'workspace_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación M:M con Terms (Taxonomy)
     * Permite asociar múltiples marcas, categorías, etc.
     */
    public function terms()
    {
        return $this->belongsToMany(
            TermModel::class,
            'catalog_item_terms',
            'item_id',
            'term_id'
        );
    }
}