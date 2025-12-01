<?php

namespace App\Taxonomy\Infrastructure\Out\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Term Relation Model
 * 
 * Note: This model extends base Model instead of EloquentModel because
 * catalog_term_relations table does not have deleted_at column.
 * Relations are hard-deleted, not soft-deleted.
 */
class TermRelationModel extends Model
{
    public $primaryKey = 'id';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    protected $table = 'catalog_term_relations';

    protected $fillable = [
        'id',
        'from_term_id',
        'to_term_id',
        'relation_type',
        'depth',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    /**
     * Get the source term of the relation
     */
    public function fromTerm()
    {
        return $this->belongsTo(TermModel::class, 'from_term_id', 'id');
    }

    /**
     * Get the target term of the relation
     */
    public function toTerm()
    {
        return $this->belongsTo(TermModel::class, 'to_term_id', 'id');
    }

    /**
     * Scope to filter by relation type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('relation_type', $type);
    }

    /**
     * Scope to filter parent relations
     */
    public function scopeParents($query)
    {
        return $query->ofType('parent');
    }

    /**
     * Scope to filter related relations
     */
    public function scopeRelated($query)
    {
        return $query->ofType('related');
    }

    /**
     * Scope to filter synonym relations
     */
    public function scopeSynonyms($query)
    {
        return $query->ofType('synonym');
    }
}
