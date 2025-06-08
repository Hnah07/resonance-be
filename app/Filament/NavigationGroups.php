<?php

namespace App\Filament;

use Filament\Navigation\NavigationGroup;

class NavigationGroups
{
    public static function make(): array
    {
        return [
            NavigationGroup::make()
                ->label('User Management')
                ->icon('heroicon-o-user-group'),
            NavigationGroup::make()
                ->label('Check-ins')
                ->icon('heroicon-o-check-circle'),
            NavigationGroup::make()
                ->label('Music Management')
                ->icon('heroicon-o-musical-note'),
        ];
    }
}
