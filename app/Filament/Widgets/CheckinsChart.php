<?php

namespace App\Filament\Widgets;

use App\Models\Checkin;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CheckinsChart extends ChartWidget
{
    protected static ?string $heading = 'Check-ins per month in 2025';

    protected function getData(): array
    {
        $checkins = Checkin::query()
            ->whereYear('created_at', 2025)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        $data = array_fill(0, 12, 0); // Initialize array with zeros for all months
        $labels = array_values($months);

        foreach ($checkins as $checkin) {
            $data[$checkin->month - 1] = $checkin->count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Check-ins',
                    'data' => $data,
                    'borderColor' => '#06B6D4',
                    'backgroundColor' => '#06B6D4',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
