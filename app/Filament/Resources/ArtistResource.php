<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Filament\Resources\ArtistResource\RelationManagers;
use App\Models\Artist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Carbon\Carbon;
use App\Filament\Resources\ArtistResource\RelationManagers\ConcertsRelationManager;
use App\Filament\Resources\ArtistResource\RelationManagers\GenresRelationManager;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Music Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return 'Artists';
    }

    public static function getPluralLabel(): string
    {
        return 'Artists';
    }

    public static function getModelLabel(): string
    {
        return 'Artist';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Artist Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('country_id')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('formed_year')
                            ->displayFormat('Y')
                            ->format('Y')
                            ->minDate(now()->subYears(150))
                            ->maxDate(now())
                            ->native(false)
                            ->columnSpan(1),
                        Forms\Components\FileUpload::make('image_url')
                            ->image()
                            ->imageEditor()
                            ->directory('artists')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('genres')
                            ->relationship('genres', 'genre')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\Select::make('source_id')
                            ->relationship('source', 'source')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status_id')
                            ->relationship('status', 'status')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.jpg')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('formed_year')
                    ->formatStateUsing(fn($state) => $state ? date('Y', strtotime($state)) : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('genres.genre')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source.source')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'manual' => 'primary',
                        'api' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status.status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending_approval' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('country')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->relationship('status', 'status')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('source')
                    ->relationship('source', 'source')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('formed_year')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('formed_year_from')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(date('Y'))
                                    ->placeholder('From year'),
                                Forms\Components\TextInput::make('formed_year_to')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(date('Y'))
                                    ->placeholder('To year'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['formed_year_from'],
                                fn(Builder $query, $year): Builder => $query->where('formed_year', '>=', $year),
                            )
                            ->when(
                                $data['formed_year_to'],
                                fn(Builder $query, $year): Builder => $query->where('formed_year', '<=', $year),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['formed_year_from'] ?? null) {
                            $indicators[] = 'Formed from ' . $data['formed_year_from'];
                        }
                        if ($data['formed_year_to'] ?? null) {
                            $indicators[] = 'Formed until ' . $data['formed_year_to'];
                        }
                        return $indicators;
                    }),
                Tables\Filters\SelectFilter::make('genres')
                    ->relationship('genres', 'genre')
                    ->searchable()
                    ->preload(),
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
            'concerts' => ConcertsRelationManager::class,
            // 'genres' => GenresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtists::route('/'),
            'create' => Pages\CreateArtist::route('/create'),
            'edit' => Pages\EditArtist::route('/{record}/edit'),
        ];
    }
}
