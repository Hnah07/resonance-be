<?php

namespace App\Filament\Resources\ArtistResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Source;
use App\Models\Status;

class ConcertsRelationManager extends RelationManager
{
    protected static string $relationship = 'concerts';

    protected static ?string $recordTitleAttribute = 'date';

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
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.city')
                    ->label('City')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status.status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending_approval' => 'warning',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('time_period')
                    ->options([
                        'past' => 'Past Concerts',
                        'future' => 'Future Concerts',
                        'all' => 'All Concerts',
                    ])
                    ->default('all')
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'past' => $query->where('date', '<', Carbon::today()),
                            'future' => $query->where('date', '>=', Carbon::today()),
                            default => $query,
                        };
                    }),
                SelectFilter::make('status')
                    ->relationship('status', 'status')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Concerts';
    }
}
