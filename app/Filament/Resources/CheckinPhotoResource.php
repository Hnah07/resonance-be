<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckinPhotoResource\Pages;
use App\Filament\Resources\CheckinPhotoResource\RelationManagers;
use App\Models\CheckinPhoto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CheckinPhotoResource extends Resource
{
    protected static ?string $model = CheckinPhoto::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Check-ins';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'caption';

    public static function getNavigationLabel(): string
    {
        return 'Check-in Photos';
    }

    public static function getPluralLabel(): string
    {
        return 'Check-in Photos';
    }

    public static function getModelLabel(): string
    {
        return 'Check-in Photo';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Photo Details')
                    ->schema([
                        Forms\Components\Select::make('checkin_id')
                            ->relationship('checkin', 'id')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                "{$record->user->name} - {$record->concert->event->name} ({$record->concert->date})"
                            )
                            ->label('Check-in'),
                        Forms\Components\FileUpload::make('url')
                            ->label('Upload a photo')
                            ->image()
                            ->imageEditor()
                            ->directory('checkin-photos')
                            ->disk('public')
                            ->visibility('public')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('caption')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->square()
                    ->defaultImageUrl(url('/images/placeholder.jpg'))
                    ->disk('public')
                    ->visibility('public')
                    ->getStateUsing(function ($record) {
                        if (str_starts_with($record->url, 'http')) {
                            return $record->url;
                        }
                        return asset('storage/' . $record->url);
                    }),
                Tables\Columns\TextColumn::make('caption')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('checkin.user.username')
                    ->searchable()
                    ->sortable()
                    ->label('User'),
                Tables\Columns\TextColumn::make('checkin.concert.event.name')
                    ->searchable()
                    ->sortable()
                    ->label('Event'),
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
                Tables\Filters\SelectFilter::make('checkin')
                    ->relationship('checkin', 'id')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckinPhotos::route('/'),
            'create' => Pages\CreateCheckinPhoto::route('/create'),
            'edit' => Pages\EditCheckinPhoto::route('/{record}/edit'),
        ];
    }
}
