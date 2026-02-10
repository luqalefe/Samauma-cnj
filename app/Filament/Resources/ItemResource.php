<?php

namespace App\Filament\Resources;

use App\Enums\ItemStatus;
use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Prêmio';
    protected static ?string $navigationLabel = 'Metas / Itens';
    protected static ?string $modelLabel = 'Item do Prêmio';
    protected static ?string $pluralModelLabel = 'Itens do Prêmio';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('eixo')
                            ->options([
                                'Governança' => 'Governança',
                                'Produtividade' => 'Produtividade',
                                'Transparência' => 'Transparência',
                                'Dados e Tecnologia' => 'Dados e Tecnologia',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('artigo')
                            ->required()
                            ->placeholder('Ex: Art. 9º, I'),

                        Forms\Components\TextInput::make('requisito')
                            ->required()
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Detalhes')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('descricao')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('alinea')
                            ->label('Alínea (Pontuação)')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('pontos_maximos')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('pontos_obtidos')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('requer_documento')
                            ->label('Requer Documento Comprobatório'),
                    ]),

                Forms\Components\Section::make('Responsabilidade')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('setor_id')
                            ->label('Setor Responsável')
                            ->relationship('setor', 'nome')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('ponto_focal')
                            ->label('Ponto Focal'),

                        Forms\Components\Select::make('responsavel_id')
                            ->label('Responsável')
                            ->relationship('responsavel', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('parent_id')
                            ->label('Item Pai')
                            ->relationship('parent', 'requisito')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),

                Forms\Components\Section::make('Status e Prazos')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(ItemStatus::class)
                            ->default(ItemStatus::NaoIniciado)
                            ->required(),

                        Forms\Components\DatePicker::make('prazo_inicio')
                            ->label('Prazo Início'),

                        Forms\Components\DatePicker::make('prazo_fim')
                            ->label('Prazo Final'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->whereNull('parent_id'))
            ->columns([
                Tables\Columns\TextColumn::make('eixo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Governança' => 'success',
                        'Produtividade' => 'info',
                        'Transparência' => 'warning',
                        'Dados e Tecnologia' => 'primary',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('artigo')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('requisito')
                    ->limit(50)
                    ->tooltip(fn(Item $record) => $record->requisito)
                    ->searchable(),

                Tables\Columns\TextColumn::make('children_count')
                    ->label('Sub-itens')
                    ->counts('children')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('setor.sigla')
                    ->label('Setor')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pontos_maximos')
                    ->label('Pts Máx')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('prazo_fim')
                    ->label('Prazo')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('eixo')
            ->filters([
                Tables\Filters\SelectFilter::make('eixo')
                    ->options([
                        'Governança' => 'Governança',
                        'Produtividade' => 'Produtividade',
                        'Transparência' => 'Transparência',
                        'Dados e Tecnologia' => 'Dados e Tecnologia',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options(ItemStatus::class),

                Tables\Filters\SelectFilter::make('setor_id')
                    ->label('Setor')
                    ->relationship('setor', 'nome')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubItensRelationManager::class,
            RelationManagers\TarefasRelationManager::class,
            RelationManagers\ComentariosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItens::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view' => Pages\ViewItem::route('/{record}'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
