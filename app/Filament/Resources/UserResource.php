<?php

namespace App\Filament\Resources;

use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Gestão';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('matricula')
                            ->label('Matrícula')
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create'),

                        Forms\Components\Select::make('setor_id')
                            ->label('Setor')
                            ->relationship('setor', 'nome')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->options(UserStatus::class)
                            ->default(UserStatus::Pendente)
                            ->required(),

                        Forms\Components\Select::make('roles')
                            ->label('Perfil')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('matricula')
                    ->label('Matrícula')
                    ->searchable(),

                Tables\Columns\TextColumn::make('setor.sigla')
                    ->label('Setor')
                    ->badge(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Perfil')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(UserStatus::class),

                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),
                Action::make('aprovar')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['status' => UserStatus::Ativo]))
                    ->visible(fn (User $record) => $record->status === UserStatus::Pendente),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
