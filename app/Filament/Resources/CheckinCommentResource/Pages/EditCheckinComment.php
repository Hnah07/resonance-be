<?php

namespace App\Filament\Resources\CheckinCommentResource\Pages;

use App\Filament\Resources\CheckinCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckinComment extends EditRecord
{
    protected static string $resource = CheckinCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
