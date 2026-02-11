<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class CnjProgressoChart extends ChartWidget
{
    protected static ?string $heading = 'Métricas para OURO';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Valor fake baseado na imagem: 113.29%
        // Ajustar lógica para ler do banco depois
        $percentage = 113.29;

        return [
            'datasets' => [
                [
                    'label' => 'Progresso Ouro',
                    'data' => [$percentage],
                    'backgroundColor' => ['#fbbf24'],
                    'borderColor' => ['#d97706'],
                    'circumference' => 180,
                    'rotation' => 270,
                ],
            ],
            'labels' => ['Ouro'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '70%',
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}
