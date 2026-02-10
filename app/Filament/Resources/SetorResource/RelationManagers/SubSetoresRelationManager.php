<?php

namespace App\Filament\Resources\SetorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubSetoresRelationManager extends RelationManager
{
    protected static string $relationship = 'children';
    protected static ?string $title = 'Sub-setores';
    protected static ?string $modelLabel = 'Sub-setor';
    protected static ?string $pluralModelLabel = 'Sub-setores';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sigla')
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('nome')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                Tables\Columns\TextColumn::make('sigla')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->sortable(),

                Tables\Columns\TextColumn::make('children_count')
                    ->label('Sub')
                    ->counts('children')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('itens_count')
                    ->label('Itens')
                    ->counts('itens')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gerentes.name')
                    ->label('Gerentes')
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('sigla')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
