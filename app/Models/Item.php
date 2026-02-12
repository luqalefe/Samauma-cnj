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
        'parent_id',
        'setor_id',
        'responsavel_id',

        // Identificação Nova
        'codigo_exibicao',
        'nome',
        'descricao',

        // Classificação Nova
        'tipo', // 'eixo', 'grupo', 'criterio'
        'ano_exercicio',
        'eixo', // Mantido para compatibilidade ou migrar para 'tipo'='eixo'
        'artigo', // Mantido
        'requisito', // Mantido
        'alinea', // Mantido

        // Lógica de Cálculo Nova
        'tipo_calculo',
        'formula_expressao',

        // Pontuação
        'pontos_maximos',
        'pontos_obtidos',
        'pontos_teto_grupo',

        // Gestão
        'ponto_focal',
        'status',
        'requer_documento',
        'prazo_inicio',
        'prazo_fim', // Usado como prazo_limite
    ];

    protected $casts = [
        'status' => ItemStatus::class,
        'requer_documento' => 'boolean',
        'prazo_inicio' => 'date',
        'prazo_fim' => 'date',
        'ano_exercicio' => 'integer',
        'pontos_maximos' => 'integer',
        'pontos_obtidos' => 'integer',
        'pontos_teto_grupo' => 'integer',
    ];

    // --- Relacionamentos Novos ---

    public function regrasTecnicas(): HasMany
    {
        return $this->hasMany(ItemRegraTecnica::class);
    }

    public function excecoes(): HasMany
    {
        return $this->hasMany(ItemExcecao::class);
    }

    public function avaliacoesMensais(): HasMany
    {
        return $this->hasMany(AvaliacaoMensal::class);
    }

    // --- Relacionamentos Existentes ---

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

    // Recursive children
    public function descendentes()
    {
        return $this->children()->with('descendentes');
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

    public function scopeComEstatisticas(Builder $query): Builder
    {
        return $query
            ->withCount([
                'tarefas',
                'tarefas as tarefas_concluidas_count' => fn($q) =>
                    $q->where('status', 'concluida'),
                'tarefas as tarefas_atrasadas_count' => fn($q) =>
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

    public function getProgressoAttribute(): float
    {
        $total = $this->tarefas_count ?? $this->tarefas()->count();
        if ($total === 0)
            return 0;

        $concluidas = $this->tarefas_concluidas_count
            ?? $this->tarefas()->where('status', 'concluida')->count();

        return round(($concluidas / $total) * 100, 1);
    }

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
