<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SetorResource\Pages;
use App\Filament\Resources\SetorResource\RelationManagers;
use App\Models\Setor;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;

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
            ->modifyQueryUsing(function (Builder $query) {
                // When no search is active, show only root setores
                // When searching, show matching setores + children of matches
                $search = request()->input('tableSearch');

                if (empty($search)) {
                    $query->whereNull('parent_id');
                } else {
                    // Show setores that match the search OR whose parent matches
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('sigla', 'like', "%{$search}%")
                            ->orWhere('nome', 'like', "%{$search}%")
                            ->orWhereHas('parent', function (Builder $parentQ) use ($search) {
                                $parentQ->where('sigla', 'like', "%{$search}%")
                                    ->orWhere('nome', 'like', "%{$search}%");
                            });
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('sigla')
                    ->badge()
                    ->color(fn($record) => $record->parent_id ? 'gray' : 'primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nome')
                    ->sortable()
                    ->description(fn($record) => $record->parent ? "↳ {$record->parent->sigla}" : null),

                Tables\Columns\TextColumn::make('children_count')
                    ->label('Seções')
                    ->counts('children')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gerentes.name')
                    ->label('Gerentes')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('itens_count')
                    ->label('Itens')
                    ->counts('itens')
                    ->sortable(),
            ])
            ->defaultSort('sigla')
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubSetoresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetores::route('/'),
            'create' => Pages\CreateSetor::route('/create'),
            'view' => Pages\ViewSetor::route('/{record}'),
            'edit' => Pages\EditSetor::route('/{record}/edit'),
        ];
    }
}
