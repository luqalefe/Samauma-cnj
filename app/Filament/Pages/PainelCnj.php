<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PainelCnj extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.painel-cnj';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CnjMetricasOverview::class,
            \App\Filament\Widgets\CnjProgressoChart::class,
            \App\Filament\Widgets\CnjEixoChart::class,
            \App\Filament\Widgets\CnjStatusChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\CnjItensTable::class,
        ];
    }
}
