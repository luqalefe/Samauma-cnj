<?php

namespace App\Filament\Widgets;

use App\Enums\TarefaStatus;
use App\Models\Tarefa;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;

class TarefasAtrasadasWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Tarefas Atrasadas (Top 10)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tarefa::query()
                    ->atrasadas()
                    ->with(['item.setor', 'responsavel'])
                    ->orderBy('data_fim_prevista')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('item.requisito')
                    ->label('Meta')
                    ->limit(35),

                Tables\Columns\TextColumn::make('descricao')
                    ->limit(40),

                Tables\Columns\TextColumn::make('item.setor.sigla')
                    ->label('Setor')
                    ->badge(),

                Tables\Columns\TextColumn::make('responsavel.name')
                    ->label('Responsável')
                    ->default(fn ($record) => $record->responsavel_nome ?? '—'),

                Tables\Columns\TextColumn::make('data_fim_prevista')
                    ->label('Venceu em')
                    ->date('d/m/Y')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->actions([
                Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->concluir()),
            ])
            ->paginated(false);
    }
}
