<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Item;

class CnjMetricasOverview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $metas = config('cnj.metas');

        // Cálculo do % de Cumprimento Ouro (Exemplo Simplificado - ajustar conforme lógica real)
        // Assumindo que Ouro se baseia em pontos totais ou média
        // Vou usar um valor hardcoded inicial com base na imagem do usuário (113%)
        // mas idealmente viria do banco.

        $pontosObtidos = Item::sum('pontos_obtidos'); // Exemplo
        $pontosMaximos = Item::sum('pontos_maximos'); // Exemplo

        return [
            Stat::make('Valor do Ouro 2025', number_format($metas['ouro_2025'], 0, ',', '.'))
                ->description('Meta para Ouro')
                ->icon('heroicon-o-trophy')
                ->color('warning'),

            Stat::make('Valor do Diamante 2025', number_format($metas['diamante_2025'], 0, ',', '.'))
                ->description('Meta para Diamante')
                ->icon('heroicon-o-sparkles')
                ->color('info'),

            Stat::make('Valor da Excelência 2025', number_format($metas['excelencia_2025'], 0, ',', '.'))
                ->description('Meta para Excelência')
                ->icon('heroicon-o-star')
                ->color('success'),
        ];
    }
}
