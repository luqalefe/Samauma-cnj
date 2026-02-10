<?php

namespace App\Filament\Widgets;

use App\Models\Chamado;
use App\Models\Item;
use App\Models\Tarefa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalItens = Item::whereNull('parent_id')->count();
        $itensConcluidos = Item::whereNull('parent_id')->where('status', 'concluido')->count();
        $progressoGeral = $totalItens > 0
            ? round(($itensConcluidos / $totalItens) * 100, 1)
            : 0;

        $tarefasAtrasadas = Tarefa::atrasadas()->count();
        $chamadosPendentes = Chamado::pendentes()->count();

        return [
            Stat::make('Progresso Geral', "{$progressoGeral}%")
                ->description("{$itensConcluidos} de {$totalItens} itens concluídos")
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success')
                ->chart([$progressoGeral, 100 - $progressoGeral]),

            Stat::make('Total de Itens', $totalItens)
                ->description('Itens do Prêmio CNJ')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('primary'),

            Stat::make('Tarefas Atrasadas', $tarefasAtrasadas)
                ->description('Prazos vencidos')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($tarefasAtrasadas > 0 ? 'danger' : 'success'),

            Stat::make('Chamados Pendentes', $chamadosPendentes)
                ->description('Aguardando resposta')
                ->descriptionIcon('heroicon-o-lifebuoy')
                ->color($chamadosPendentes > 0 ? 'warning' : 'success'),
        ];
    }
}
