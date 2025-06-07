<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckinCommentResource\Pages;
use App\Filament\Resources\CheckinCommentResource\RelationManagers;
use App\Models\CheckinComment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CheckinCommentResource extends Resource
{
    protected static ?string $model = CheckinComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Check-ins';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'comment';

    public static function getNavigationLabel(): string
    {
        return 'Check-in Comments';
    }

    public static function getPluralLabel(): string
    {
        return 'Check-in Comments';
    }

    public static function getModelLabel(): string
    {
        return 'Check-in Comment';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Comment Details')
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
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'username')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('User'),
                        Forms\Components\Textarea::make('comment')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.username')
                    ->searchable()
                    ->sortable()
                    ->label('Commenter')
                    ->description(fn($record) => $record->user->name),
                Tables\Columns\TextColumn::make('checkin.user.username')
                    ->searchable()
                    ->sortable()
                    ->label('Check-in Owner')
                    ->description(fn($record) => $record->checkin->user->name),
                Tables\Columns\TextColumn::make('checkin.concert.event.name')
                    ->searchable()
                    ->sortable()
                    ->label('Event'),
                Tables\Columns\TextColumn::make('checkin.concert.date')
                    ->date()
                    ->sortable()
                    ->label('Concert Date'),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
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
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'username')
                    ->searchable()
                    ->preload()
                    ->label('Filter by User'),
                Tables\Filters\SelectFilter::make('checkin')
                    ->relationship('checkin.concert.event', 'name')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckinComments::route('/'),
            'create' => Pages\CreateCheckinComment::route('/create'),
            'edit' => Pages\EditCheckinComment::route('/{record}/edit'),
        ];
    }
}
