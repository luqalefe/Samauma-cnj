<?php

namespace App\Filament\Widgets;

use App\Models\Item;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class CnjItensTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Item::query()
                    ->whereNotNull('artigo') // Filtra apenas itens principais se necessário
            )
            ->columns([
                TextColumn::make('artigo')
                    ->label('Artigo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('requisito')
                    ->label('Requisito')
                    ->limit(50)
                    ->tooltip(fn(Item $record): string => $record->descricao ?? '')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'nao_iniciado',
                        'warning' => 'em_andamento',
                        'success' => 'concluida',
                    ]),

                TextInputColumn::make('pontos_maximos')
                    ->label('Pts')
                    ->type('number')
                    ->sortable(),

                TextColumn::make('pontos_obtidos')
                    ->label('Obtidos')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('percent_cumprimento')
                    ->label('% Cumprimento')
                    ->state(function (Item $record): string {
                        if ($record->pontos_maximos > 0) {
                            $pct = ($record->pontos_obtidos / $record->pontos_maximos) * 100;
                            return number_format($pct, 2) . '%';
                        }
                        return '0%';
                    })
                    ->color(
                        fn(string $state): string =>
                        floatval($state) >= 100 ? 'success' : (floatval($state) > 0 ? 'warning' : 'danger')
                    ),
            ])
            ->filters([
                SelectFilter::make('eixo')
                    ->options(fn() => Item::select('eixo')->distinct()->pluck('eixo', 'eixo')->toArray()),
                SelectFilter::make('status')
                    ->options([
                        'nao_iniciado' => 'Não Iniciado',
                        'em_andamento' => 'Em Andamento',
                        'concluida' => 'Concluída',
                    ]),
            ]);
    }
}
