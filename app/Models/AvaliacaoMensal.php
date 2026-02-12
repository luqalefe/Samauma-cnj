<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvaliacaoMensal extends Model
{
    use HasFactory;

    protected $table = 'avaliacoes_mensais';

    public $timestamps = false; // Tabela não tem created_at/updated_at por padrão no DBML, mas adicionei data_calculo

    protected $fillable = [
        'item_id',
        'mes_referencia',
        'percentual_alcancado',
        'pontos_conquistados',
        'data_calculo',
        'log_calculo',
    ];

    protected $casts = [
        'mes_referencia' => 'date',
        'percentual_alcancado' => 'decimal:2',
        'pontos_conquistados' => 'integer',
        'data_calculo' => 'datetime',
        'log_calculo' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
