<?php

namespace App\Filament\Resources\CheckinCommentResource\Pages;

use App\Filament\Resources\CheckinCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckinComment extends CreateRecord
{
    protected static string $resource = CheckinCommentResource::class;
}
