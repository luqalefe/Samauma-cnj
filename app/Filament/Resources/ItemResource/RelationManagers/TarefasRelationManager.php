<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Enums\TarefaStatus;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TarefasRelationManager extends RelationManager
{
    protected static string $relationship = 'tarefas';
    protected static ?string $title = 'Tarefas / Plano de Ação';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Textarea::make('descricao')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Select::make('responsavel_id')
                    ->label('Responsável (Usuário)')
                    ->relationship('responsavel', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('responsavel_nome')
                    ->label('Responsável (Nome externo)')
                    ->placeholder('Se não for um usuário do sistema'),

                Forms\Components\DatePicker::make('data_inicio')
                    ->label('Início'),

                Forms\Components\DatePicker::make('data_fim_prevista')
                    ->label('Prazo Final')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options(TarefaStatus::class)
                    ->default(TarefaStatus::Pendente)
                    ->required(),

                Forms\Components\Textarea::make('observacoes')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descricao')
                    ->limit(60)
                    ->searchable(),

                Tables\Columns\TextColumn::make('responsavel.name')
                    ->label('Responsável')
                    ->default(fn ($record) => $record->responsavel_nome ?? '—'),

                Tables\Columns\TextColumn::make('data_fim_prevista')
                    ->label('Prazo')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record->estaAtrasada() ? 'danger' : null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('data_fim_prevista')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->concluir())
                    ->visible(fn ($record) => $record->status !== TarefaStatus::Concluida),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
