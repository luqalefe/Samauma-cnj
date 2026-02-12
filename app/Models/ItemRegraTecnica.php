<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemRegraTecnica extends Model
{
    use HasFactory;

    protected $table = 'itens_regras_tecnicas';

    protected $fillable = [
        'item_id',
        'segmento_justica',
        'variavel_codinome',
        'campo_analise',
        'operador_logico',
        'meta_percentual',
        'pontos_se_atingido',
        'descricao_tecnica',
        'query_config_json',
    ];

    protected $casts = [
        'meta_percentual' => 'decimal:2',
        'pontos_se_atingido' => 'integer',
        'query_config_json' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
