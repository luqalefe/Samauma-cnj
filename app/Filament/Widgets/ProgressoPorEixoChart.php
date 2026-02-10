<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Widgets\ChartWidget;

class ProgressoPorEixoChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Progresso por Eixo';

    protected function getData(): array
    {
        $eixos = Item::query()
            ->whereNull('parent_id')
            ->selectRaw("eixo, COUNT(*) as total, SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos")
            ->groupBy('eixo')
            ->orderBy('eixo')
            ->get();

        $labels = $eixos->pluck('eixo')->toArray();
        $totais = $eixos->pluck('total')->toArray();
        $concluidos = $eixos->pluck('concluidos')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total de Itens',
                    'data' => $totais,
                    'backgroundColor' => '#60a5fa',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Concluídos',
                    'data' => $concluidos,
                    'backgroundColor' => '#34d399',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
