<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Item;

class CnjStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status dos Itens';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $concluido = Item::where('status', 'concluida')->count(); // Ajustar valor do Enum se necessário
        $pendente = Item::where('status', 'nao_iniciado')->count();
        $emAndamento = Item::where('status', 'em_andamento')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Itens',
                    'data' => [$concluido, $pendente, $emAndamento],
                    'backgroundColor' => [
                        '#22c55e', // Green-500
                        '#fca5a5', // Red-300 (Pendente/Atrasado?)
                        '#fde047', // Yellow-300
                    ],
                ],
            ],
            'labels' => ['Concluído', 'Pendente', 'Em Andamento'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
