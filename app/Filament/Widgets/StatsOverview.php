<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Artist;
use App\Models\Event;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Total number of registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('secondary'),
            Stat::make('Total Artists', Artist::count())
                ->description('Total number of artists')
                ->descriptionIcon('heroicon-m-musical-note')
                ->color('primary'),
            Stat::make('Total Events', Event::count())
                ->description('Total number of events')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}
