<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckinResource\Pages;
use App\Filament\Resources\CheckinResource\RelationManagers;
use App\Models\Checkin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CheckinResource extends Resource
{
    protected static ?string $model = Checkin::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Check-ins';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return 'Check-ins';
    }

    public static function getPluralLabel(): string
    {
        return 'Check-ins';
    }

    public static function getModelLabel(): string
    {
        return 'Check-in';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Check-in Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        Forms\Components\Select::make('concert_id')
                            ->relationship('concert', 'date')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Concert')
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                "{$record->event->name} at {$record->location->name} on " .
                                    date('d/m/Y', strtotime($record->date))
                            )
                            ->live(),
                        Forms\Components\Select::make('artists')
                            ->relationship('artists', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->label('Artists Seen')
                            ->helperText('Select the artists you have seen at this concert')
                            ->options(function (Forms\Get $get) {
                                $concertId = $get('concert_id');
                                if (!$concertId) {
                                    return [];
                                }
                                return \App\Models\Artist::whereHas('concerts', function ($query) use ($concertId) {
                                    $query->where('concert_id', $concertId);
                                })
                                    ->get()
                                    ->mapWithKeys(function ($artist) {
                                        return [$artist->id => "{$artist->name} ({$artist->country->name})"];
                                    });
                            })
                            ->visible(fn(Forms\Get $get) => filled($get('concert_id')))
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Photos')
                    ->schema([
                        Forms\Components\Repeater::make('photos')
                            ->relationship('photos')
                            ->schema([
                                Forms\Components\FileUpload::make('url')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('checkins')
                                    ->required(),
                                Forms\Components\TextInput::make('caption')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->searchable()
                    ->sortable()
                    ->label('User')
                    ->description(fn($record) => $record->user->name),
                Tables\Columns\TextColumn::make('concert.event.name')
                    ->searchable()
                    ->sortable()
                    ->label('Event'),
                Tables\Columns\TextColumn::make('concert.location.name')
                    ->searchable()
                    ->sortable()
                    ->label('Location'),
                Tables\Columns\TextColumn::make('concert.date')
                    ->date()
                    ->sortable()
                    ->label('Date'),
                Tables\Columns\TextColumn::make('artists.name')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->label('Artists Seen'),
                Tables\Columns\ImageColumn::make('photos.url')
                    ->stacked()
                    ->limit(3)
                    ->square()
                    ->label('Photos'),
                Tables\Columns\TextColumn::make('likes_count')
                    ->counts('likes')
                    ->sortable()
                    ->label('Likes'),
                Tables\Columns\TextColumn::make('comments_count')
                    ->counts('comments')
                    ->sortable()
                    ->label('Comments'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'username')
                    ->searchable()
                    ->preload()
                    ->label('Filter by User'),
                Tables\Filters\SelectFilter::make('concert')
                    ->relationship('concert.event', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Event'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder('From date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder('Until date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Created from ' . $data['created_from'];
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Created until ' . $data['created_until'];
                        }
                        return $indicators;
                    }),
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
            RelationManagers\PhotosRelationManager::class,
            RelationManagers\LikesRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\ArtistCheckinsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckins::route('/'),
            'create' => Pages\CreateCheckin::route('/create'),
            'edit' => Pages\EditCheckin::route('/{record}/edit'),
        ];
    }
}
