<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConcertResource\Pages;
use App\Filament\Resources\ConcertResource\RelationManagers;
use App\Models\Concert;
use App\Models\Source;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ConcertResource\RelationManagers\ArtistsRelationManager;

class ConcertResource extends Resource
{
    protected static ?string $model = Concert::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Music Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'date';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('start_date')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'festival' => 'Festival',
                                'concert' => 'Concert',
                                'tour' => 'Tour',
                                'clubnight' => 'Club Night',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('image_url')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('source_id')
                            ->default(fn() => Source::where('source', 'Manual')->first()?->id),
                        Forms\Components\Hidden::make('status_id')
                            ->default(fn() => Status::where('status', 'Verified')->first()?->id),
                    ]),
                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->minDate(now()->startOfDay()),
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('street')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('house_number')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric(),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric(),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('source_id')
                            ->default(fn() => Source::where('source', 'Manual')->first()?->id),
                        Forms\Components\Hidden::make('status_id')
                            ->default(fn() => Status::where('status', 'Verified')->first()?->id),
                    ]),
                Forms\Components\Hidden::make('source_id')
                    ->default(fn() => Source::where('source', 'Manual')->first()?->id),
                Forms\Components\Hidden::make('status_id')
                    ->default(fn() => Status::where('status', 'Verified')->first()?->id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source.source')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.status')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('status')
                    ->relationship('status', 'status'),
                Tables\Filters\SelectFilter::make('source')
                    ->relationship('source', 'source'),
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'name'),
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
            'artists' => ArtistsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConcerts::route('/'),
            'create' => Pages\CreateConcert::route('/create'),
            'edit' => Pages\EditConcert::route('/{record}/edit'),
        ];
    }
}
