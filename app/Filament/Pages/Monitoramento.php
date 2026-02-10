<?php

namespace App\Filament\Pages;

use App\Models\Item;
use App\Models\Setor;
use Filament\Pages\Page;

class Monitoramento extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Prêmio';
    protected static ?string $navigationLabel = 'Monitoramento';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.monitoramento';

    public ?string $eixoFilter = null;
    public ?string $setorFilter = null;

    public function getViewData(): array
    {
        $query = Item::query()
            ->comEstatisticas()
            ->raiz();

        if ($this->eixoFilter) {
            $query->where('eixo', $this->eixoFilter);
        }

        if ($this->setorFilter) {
            $query->where('setor_id', $this->setorFilter);
        }

        $itens = $query->orderBy('eixo')->orderBy('artigo')->get();

        // Group by eixo
        $eixoGroups = $itens->groupBy('eixo')->map(function ($group) {
            $total = $group->count();
            $concluidos = $group->where('status.value', 'concluido')->count();
            $progresso = $total > 0 ? round(($concluidos / $total) * 100, 1) : 0;

            return [
                'itens' => $group,
                'total' => $total,
                'concluidos' => $concluidos,
                'progresso' => $progresso,
            ];
        });

        return [
            'eixoGroups' => $eixoGroups,
            'eixos' => Item::eixosDisponiveis(),
            'setores' => Setor::orderBy('nome')->pluck('nome', 'id')->toArray(),
        ];
    }
}
