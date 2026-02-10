<?php

namespace App\Models;

use App\Enums\ChamadoStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chamado extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'solicitante_id',
        'nivel_destino',
        'mensagem',
        'resposta',
        'status',
        'respondido_por',
        'respondido_em',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChamadoStatus::class,
            'respondido_em' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function respondidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondido_por');
    }

    // --- Scopes ---

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', ChamadoStatus::Pendente);
    }

    // --- Helpers ---

    public function resolver(User $user, string $resposta): void
    {
        $this->update([
            'status' => ChamadoStatus::Resolvido,
            'resposta' => $resposta,
            'respondido_por' => $user->id,
            'respondido_em' => now(),
        ]);
    }
}
