<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ComentariosRelationManager extends RelationManager
{
    protected static string $relationship = 'comentarios';
    protected static ?string $title = 'Comentários / Orientações';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Textarea::make('mensagem')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Select::make('tipo')
                    ->options([
                        'orientacao' => 'Orientação',
                        'cobranca' => 'Cobrança',
                        'elogio' => 'Elogio',
                    ])
                    ->default('orientacao')
                    ->required(),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Autor'),

                Tables\Columns\TextColumn::make('mensagem')
                    ->limit(80),

                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'orientacao' => 'info',
                        'cobranca' => 'warning',
                        'elogio' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
