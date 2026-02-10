<?php

namespace App\Filament\Resources;

use App\Enums\ChamadoStatus;
use App\Filament\Resources\ChamadoResource\Pages;
use App\Models\Chamado;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;

class ChamadoResource extends Resource
{
    protected static ?string $model = Chamado::class;

    protected static ?string $navigationIcon = 'heroicon-o-lifebuoy';
    protected static ?string $navigationGroup = 'Comunicação';
    protected static ?string $navigationLabel = 'Chamados';
    protected static ?string $modelLabel = 'Chamado';
    protected static ?string $pluralModelLabel = 'Chamados';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Solicitação')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Item Relacionado')
                            ->relationship('item', 'requisito')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('nivel_destino')
                            ->label('Destinatário')
                            ->options([
                                'gerente' => 'Gerente',
                                'admin' => 'Administração',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('mensagem')
                            ->required()
                            ->rows(4),

                        Forms\Components\Hidden::make('solicitante_id')
                            ->default(fn () => auth()->id()),
                    ]),

                Forms\Components\Section::make('Resposta')
                    ->hidden(fn (string $operation) => $operation === 'create')
                    ->schema([
                        Forms\Components\Textarea::make('resposta')
                            ->rows(4),

                        Forms\Components\Select::make('status')
                            ->options(ChamadoStatus::class)
                            ->default(ChamadoStatus::Pendente),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item.requisito')
                    ->label('Item')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('solicitante.name')
                    ->label('Solicitante'),

                Tables\Columns\TextColumn::make('nivel_destino')
                    ->label('Destino')
                    ->badge()
                    ->color(fn (string $state) => $state === 'admin' ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aberto em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ChamadoStatus::class),
            ])
            ->actions([
                EditAction::make(),
                Action::make('responder')
                    ->label('Responder')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('resposta')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(fn (Chamado $record, array $data) =>
                        $record->resolver(auth()->user(), $data['resposta'])
                    )
                    ->visible(fn (Chamado $record) => $record->status === ChamadoStatus::Pendente),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChamados::route('/'),
            'create' => Pages\CreateChamado::route('/create'),
            'edit' => Pages\EditChamado::route('/{record}/edit'),
        ];
    }
}
