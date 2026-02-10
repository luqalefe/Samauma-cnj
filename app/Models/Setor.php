<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'setores';

    protected $fillable = [
        'nome',
        'sigla',
        'parent_id',
    ];

    // --- Relationships ---

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Setor::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Setor::class, 'parent_id');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'setor_id');
    }

    public function gerentes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'setor_user')
                     ->withTimestamps();
    }

    public function itens(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    // --- Scopes ---

    /**
     * Eager-load full structure: gerentes, itens with statistics.
     * Resolves the N+1 problem from the legacy system (60→3 queries).
     */
    public function scopeComEstrutura(Builder $query): Builder
    {
        return $query
            ->with(['gerentes:id,name,email'])
            ->withCount('itens')
            ->with(['itens' => fn ($q) => $q->comEstatisticas()]);
    }

    public function scopeRaiz(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
