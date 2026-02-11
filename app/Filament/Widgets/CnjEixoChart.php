<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class CnjEixoChart extends ChartWidget
{
    protected static ?string $heading = 'Pontos por Eixo';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Agrupar por Eixo
        // Eixos esperados: Governança, Produtividade, Dados e Tecnologia, Transparência

        $eixos = ['Governança', 'Produtividade', 'Dados e Tecnologia', 'Transparência'];

        // Mock de dados (substituir por query real)
        $pontosObtidos = [];
        $pontosPrevistos = []; // Precisa definir lógica de previstos no Item ou constante
        $pontosMaximos = [];

        foreach ($eixos as $eixo) {
            $pontosObtidos[] = Item::where('eixo', $eixo)->sum('pontos_obtidos');
            $pontosMaximos[] = Item::where('eixo', $eixo)->sum('pontos_maximos');
            // Previsto como 90% do máximo para exemplo
            $pontosPrevistos[] = floor(Item::where('eixo', $eixo)->sum('pontos_maximos') * 0.9);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pontos Obtidos 2024',
                    'data' => $pontosObtidos,
                    'backgroundColor' => '#93c5fd', // Blue-300
                ],
                [
                    'label' => 'Pontos Previstos 2025',
                    'data' => $pontosPrevistos,
                    'backgroundColor' => '#86efac', // Green-300
                ],
                [
                    'label' => 'Pontos Max.',
                    'data' => $pontosMaximos,
                    'backgroundColor' => '#2dd4bf', // Teal-400
                ],
            ],
            'labels' => $eixos,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
