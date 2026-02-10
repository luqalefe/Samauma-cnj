<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Enums\ItemStatus;
use App\Enums\TarefaStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class SubItensRelationManager extends RelationManager
{
    protected static string $relationship = 'children';
    protected static ?string $title = 'Sub-itens';
    protected static ?string $modelLabel = 'Sub-item';
    protected static ?string $pluralModelLabel = 'Sub-itens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('alinea')
                    ->label('Alínea')
                    ->placeholder('Ex: a.1)')
                    ->maxLength(10),

                Forms\Components\TextInput::make('requisito')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('descricao')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('pontos_maximos')
                    ->label('Pts Máx')
                    ->numeric()
                    ->default(0),

                Forms\Components\Select::make('status')
                    ->options(ItemStatus::class)
                    ->default(ItemStatus::NaoIniciado),

                Forms\Components\Select::make('responsavel_id')
                    ->label('Responsável')
                    ->relationship('responsavel', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('requisito')
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('#')
                    ->rowIndex()
                    ->width(40),

                Tables\Columns\TextColumn::make('alinea')
                    ->label('Alínea')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->descricao)
                    ->wrap(),

                Tables\Columns\TextColumn::make('pontos_maximos')
                    ->label('Pts')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('children_count')
                    ->label('Sub')
                    ->counts('children')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tarefas_count')
                    ->label('Tarefas')
                    ->counts('tarefas')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),
            ])
            ->defaultSort('alinea')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $parent = $this->getOwnerRecord();
                        $data['eixo'] = $parent->eixo;
                        $data['artigo'] = $parent->artigo;
                        $data['requer_documento'] = $parent->requer_documento;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // ── Ação: Criar Tarefa a partir do sub-item ──
                Tables\Actions\Action::make('criarTarefa')
                    ->label('Criar Tarefa')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição da Tarefa')
                            ->required()
                            ->rows(3)
                            ->default(fn($record) => $record->descricao
                                ? mb_substr(strip_tags($record->descricao), 0, 200)
                                : $record->requisito),

                        Forms\Components\Select::make('responsavel_id')
                            ->label('Responsável')
                            ->relationship('responsavel', 'name', modifyQueryUsing: fn($query) => $query)
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('responsavel_nome')
                            ->label('Responsável (nome externo)')
                            ->placeholder('Se não for um usuário do sistema'),

                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('Início')
                            ->default(now()),

                        Forms\Components\DatePicker::make('data_fim_prevista')
                            ->label('Prazo Final')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options(TarefaStatus::class)
                            ->default(TarefaStatus::Pendente)
                            ->required(),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->tarefas()->create($data);

                        Notification::make()
                            ->title('Tarefa criada')
                            ->body("Tarefa criada para: {$record->alinea}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // ── Bulk: Criar tarefa para cada sub-item selecionado ──
                    Tables\Actions\BulkAction::make('criarTarefasEmLote')
                        ->label('Criar Tarefas')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            Forms\Components\Select::make('responsavel_id')
                                ->label('Responsável')
                                ->relationship('responsavel', 'name', modifyQueryUsing: fn($query) => $query)
                                ->searchable()
                                ->preload()
                                ->nullable(),

                            Forms\Components\DatePicker::make('data_fim_prevista')
                                ->label('Prazo Final')
                                ->required(),
                        ])
                        ->action(function ($records, array $data): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->tarefas()->create([
                                    'descricao' => $record->descricao
                                        ? mb_substr(strip_tags($record->descricao), 0, 200)
                                        : $record->requisito,
                                    'responsavel_id' => $data['responsavel_id'],
                                    'data_inicio' => now(),
                                    'data_fim_prevista' => $data['data_fim_prevista'],
                                    'status' => TarefaStatus::Pendente,
                                ]);
                                $count++;
                            }

                            Notification::make()
                                ->title("{$count} tarefas criadas")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
