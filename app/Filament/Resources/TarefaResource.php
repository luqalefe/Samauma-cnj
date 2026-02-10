<?php

namespace App\Filament\Resources;

use App\Enums\TarefaStatus;
use App\Filament\Resources\TarefaResource\Pages;
use App\Models\Tarefa;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;

class TarefaResource extends Resource
{
    protected static ?string $model = Tarefa::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Prêmio';
    protected static ?string $navigationLabel = 'Tarefas';
    protected static ?string $modelLabel = 'Tarefa';
    protected static ?string $pluralModelLabel = 'Tarefas';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Item / Meta')
                            ->relationship('item', 'requisito')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options(TarefaStatus::class)
                            ->default(TarefaStatus::Pendente)
                            ->required(),

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
                            ->label('Responsável (Externo)'),

                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('Início'),

                        Forms\Components\DatePicker::make('data_fim_prevista')
                            ->label('Prazo Final')
                            ->required(),

                        Forms\Components\Textarea::make('observacoes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.requisito')
                    ->label('Meta')
                    ->limit(40)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->limit(50)
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
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(TarefaStatus::class),

                Tables\Filters\Filter::make('atrasadas')
                    ->label('Apenas Atrasadas')
                    ->query(fn ($query) => $query->atrasadas())
                    ->toggle(),

                Tables\Filters\SelectFilter::make('item')
                    ->relationship('item', 'requisito')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->concluir())
                    ->visible(fn ($record) => $record->status !== TarefaStatus::Concluida),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTarefas::route('/'),
            'create' => Pages\CreateTarefa::route('/create'),
            'edit' => Pages\EditTarefa::route('/{record}/edit'),
        ];
    }
}
