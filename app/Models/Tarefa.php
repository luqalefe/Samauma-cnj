<?php

namespace App\Models;

use App\Enums\TarefaStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarefa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'descricao',
        'responsavel_id',
        'responsavel_nome',
        'data_inicio',
        'data_fim_prevista',
        'data_entrega_real',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'status' => TarefaStatus::class,
            'data_inicio' => 'date',
            'data_fim_prevista' => 'date',
            'data_entrega_real' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function comentarios(): MorphMany
    {
        return $this->morphMany(Comentario::class, 'commentable');
    }

    // --- Scopes ---

    public function scopeAtrasadas(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', TarefaStatus::Concluida)
            ->where('data_fim_prevista', '<', now());
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', TarefaStatus::Pendente);
    }

    public function scopeDoSetor(Builder $query, int $setorId): Builder
    {
        return $query->whereHas('item', fn ($q) =>
            $q->where('setor_id', $setorId)
        );
    }

    public function scopeDoUsuario(Builder $query, int $userId): Builder
    {
        return $query->where('responsavel_id', $userId);
    }

    // --- Helpers ---

    public function estaAtrasada(): bool
    {
        return $this->status !== TarefaStatus::Concluida
            && $this->data_fim_prevista?->isPast();
    }

    public function concluir(): void
    {
        $this->update([
            'status' => TarefaStatus::Concluida,
            'data_entrega_real' => now(),
        ]);
    }
}
