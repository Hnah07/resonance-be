<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FollowerResource\Pages;
use App\Filament\Resources\FollowerResource\RelationManagers;
use App\Models\Follower;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FollowerResource extends Resource
{
    protected static ?string $model = Follower::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return 'Followers';
    }

    public static function getPluralLabel(): string
    {
        return 'Followers';
    }

    public static function getModelLabel(): string
    {
        return 'Follower';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Follower Details')
                    ->schema([
                        Forms\Components\Select::make('follower_id')
                            ->relationship('follower', 'username')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Follower'),
                        Forms\Components\Select::make('followed_id')
                            ->relationship('followed', 'username')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Followed User'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('follower.username')
                    ->searchable()
                    ->sortable()
                    ->label('Follower'),
                Tables\Columns\TextColumn::make('followed.username')
                    ->searchable()
                    ->sortable()
                    ->label('Followed User'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFollowers::route('/'),
            'create' => Pages\CreateFollower::route('/create'),
            'edit' => Pages\EditFollower::route('/{record}/edit'),
        ];
    }
}
