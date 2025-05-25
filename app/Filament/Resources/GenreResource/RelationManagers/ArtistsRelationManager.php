<?php

namespace App\Filament\Resources\GenreResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ArtistsRelationManager extends RelationManager
{
    protected static string $relationship = 'artists';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
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
                    ->native(false),
                Forms\Components\FileUpload::make('image_url')
                    ->image()
                    ->imageEditor()
                    ->directory('artists')
                    ->columnSpanFull(),
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
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('country.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('formed_year')
                    ->formatStateUsing(fn($state) => $state ? date('Y', strtotime($state)) : null)
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
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name']),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
