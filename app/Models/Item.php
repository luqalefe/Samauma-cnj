<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'itens';

    protected $fillable = [
        'eixo',
        'artigo',
        'requisito',
        'descricao',
        'alinea',
        'pontos_maximos',
        'pontos_obtidos',
        'requer_documento',
        'parent_id',
        'setor_id',
        'responsavel_id',
        'ponto_focal',
        'status',
        'prazo_inicio',
        'prazo_fim',
    ];

    protected function casts(): array
    {
        return [
            'status' => ItemStatus::class,
            'requer_documento' => 'boolean',
            'prazo_inicio' => 'date',
            'prazo_fim' => 'date',
        ];
    }

    // --- Relationships ---

    public function setor(): BelongsTo
    {
        return $this->belongsTo(Setor::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Item::class, 'parent_id');
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class);
    }

    public function comentarios(): MorphMany
    {
        return $this->morphMany(Comentario::class, 'commentable');
    }

    public function chamados(): HasMany
    {
        return $this->hasMany(Chamado::class);
    }

    // --- Scopes ---

    /**
     * Eager-load task statistics using withCount subqueries.
     * One query instead of N+1 correlated subqueries.
     */
    public function scopeComEstatisticas(Builder $query): Builder
    {
        return $query
            ->withCount([
                'tarefas',
                'tarefas as tarefas_concluidas_count' => fn ($q) =>
                    $q->where('status', 'concluida'),
                'tarefas as tarefas_atrasadas_count' => fn ($q) =>
                    $q->where('status', '!=', 'concluida')
                      ->where('data_fim_prevista', '<', now()),
            ])
            ->with(['setor:id,nome,sigla', 'responsavel:id,name']);
    }

    public function scopePorEixo(Builder $query, string $eixo): Builder
    {
        return $query->where('eixo', $eixo);
    }

    public function scopeRaiz(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    // --- Accessors ---

    public function getProgressoAttribute(): float
    {
        $total = $this->tarefas_count ?? $this->tarefas()->count();
        if ($total === 0) return 0;

        $concluidas = $this->tarefas_concluidas_count
            ?? $this->tarefas()->where('status', 'concluida')->count();

        return round(($concluidas / $total) * 100, 1);
    }

    // --- Helpers ---

    public static function eixosDisponiveis(): array
    {
        return self::query()
            ->distinct()
            ->pluck('eixo')
            ->sort()
            ->values()
            ->toArray();
    }
}
