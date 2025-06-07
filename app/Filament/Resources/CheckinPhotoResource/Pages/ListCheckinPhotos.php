<?php

namespace App\Filament\Resources\CheckinPhotoResource\Pages;

use App\Filament\Resources\CheckinPhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheckinPhotos extends ListRecords
{
    protected static string $resource = CheckinPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
