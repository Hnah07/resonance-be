<?php

namespace App\Filament\Resources\CheckinResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ArtistCheckinsRelationManager extends RelationManager
{
    protected static string $relationship = 'artists';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('artist_id')
                    ->relationship('artist', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Artist')
                    ->getOptionLabelFromRecordUsing(
                        fn($record) =>
                        "{$record->name} ({$record->country->name})"
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.jpg')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable()
                    ->label('Country'),
                Tables\Columns\TextColumn::make('genres.genre')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('formed_year')
                    ->formatStateUsing(fn($state) => $state ? date('Y', strtotime($state)) : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Country'),
                Tables\Filters\SelectFilter::make('genres')
                    ->relationship('genres', 'genre')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Genre'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Add Artist')
                    ->modalHeading('Add Artist to Check-in')
                    ->modalDescription('Select an artist that was seen at this concert')
                    ->modalSubmitActionLabel('Add Artist'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove Artist')
                    ->modalHeading('Remove Artist from Check-in')
                    ->modalDescription('Are you sure you want to remove this artist from the check-in?')
                    ->modalSubmitActionLabel('Remove Artist'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Remove Selected Artists'),
                ]),
            ]);
    }
}
