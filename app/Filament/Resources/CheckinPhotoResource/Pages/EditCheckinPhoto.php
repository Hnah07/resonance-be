<?php

namespace App\Filament\Resources\CheckinPhotoResource\Pages;

use App\Filament\Resources\CheckinPhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckinPhoto extends EditRecord
{
    protected static string $resource = CheckinPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
