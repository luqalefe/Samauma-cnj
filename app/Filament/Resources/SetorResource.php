<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetorResource\Pages;
use App\Models\Setor;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class SetorResource extends Resource
{
    protected static ?string $model = Setor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Gestão';
    protected static ?string $navigationLabel = 'Setores';
    protected static ?string $modelLabel = 'Setor';
    protected static ?string $pluralModelLabel = 'Setores';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->required(),

                        Forms\Components\TextInput::make('sigla')
                            ->placeholder('Ex: SEGEP'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Setor Pai')
                            ->relationship('parent', 'nome')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('gerentes')
                            ->label('Gerentes')
                            ->relationship('gerentes', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sigla')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.nome')
                    ->label('Setor Pai')
                    ->default('—'),

                Tables\Columns\TextColumn::make('gerentes.name')
                    ->label('Gerentes')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('itens_count')
                    ->label('Itens')
                    ->counts('itens')
                    ->sortable(),
            ])
            ->defaultSort('nome')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetores::route('/'),
            'create' => Pages\CreateSetor::route('/create'),
            'edit' => Pages\EditSetor::route('/{record}/edit'),
        ];
    }
}
