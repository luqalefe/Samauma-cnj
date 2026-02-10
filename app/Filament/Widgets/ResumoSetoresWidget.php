<?php

namespace App\Filament\Widgets;

use App\Models\Setor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ResumoSetoresWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Resumo por Setor';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Setor::query()
                    ->withCount([
                        'itens',
                        'itens as itens_concluidos_count' => fn ($q) =>
                            $q->where('status', 'concluido'),
                        'itens as itens_andamento_count' => fn ($q) =>
                            $q->where('status', 'em_andamento'),
                    ])
                    ->having('itens_count', '>', 0)
                    ->orderByDesc('itens_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('sigla')
                    ->label('Setor')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->limit(35),

                Tables\Columns\TextColumn::make('itens_count')
                    ->label('Total')
                    ->sortable(),

                Tables\Columns\TextColumn::make('itens_concluidos_count')
                    ->label('Concl.')
                    ->color('success'),

                Tables\Columns\TextColumn::make('itens_andamento_count')
                    ->label('Andamento')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('progresso')
                    ->label('Progresso')
                    ->getStateUsing(function ($record) {
                        if ($record->itens_count === 0) return '0%';
                        return round(($record->itens_concluidos_count / $record->itens_count) * 100) . '%';
                    })
                    ->badge()
                    ->color(fn ($state) => (int) $state >= 70 ? 'success' : ((int) $state >= 40 ? 'warning' : 'danger')),
            ])
            ->paginated(false);
    }
}
