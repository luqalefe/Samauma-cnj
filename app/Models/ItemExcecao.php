<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemExcecao extends Model
{
    use HasFactory;

    protected $table = 'itens_excecoes';

    protected $fillable = [
        'item_id',
        'tipo_excecao',
        'codigo_referencia',
        'descricao',
        'segmento_justica',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
