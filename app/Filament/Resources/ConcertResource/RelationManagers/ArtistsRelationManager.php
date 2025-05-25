<?php

namespace App\Filament\Resources\ConcertResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ArtistsRelationManager extends RelationManager
{
    protected static string $relationship = 'artists';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // No form needed for this relationship manager
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country.name')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('formed_year')
                    ->formatStateUsing(fn($state) => $state ? date('Y', strtotime($state)) : null)
                    ->sortable(),
                TextColumn::make('source.source')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'manual' => 'primary',
                        'api' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status.status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending_approval' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->relationship('status', 'status')
                    ->preload(),
                SelectFilter::make('source')
                    ->relationship('source', 'source')
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Artists';
    }
}
