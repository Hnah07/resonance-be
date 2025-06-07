<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Event;
use App\Models\Artist;
use App\Models\Location;
use App\Models\Concert;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class StatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Distribution';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        // Get all statuses
        $statuses = Status::all()->pluck('status')->toArray();

        // Initialize data array with all statuses
        $data = array_fill_keys($statuses, 0);

        // Count statuses from each model
        $models = [
            new Event(),
            new Artist(),
            new Location(),
            new Concert(),
        ];

        foreach ($models as $model) {
            $counts = $model::query()
                ->join('statuses', 'statuses.id', '=', $model->getTable() . '.status_id')
                ->select('statuses.status', DB::raw('count(*) as count'))
                ->groupBy('statuses.status')
                ->get();

            foreach ($counts as $count) {
                $data[$count->status] += $count->count;
            }
        }

        // Prepare data for chart
        $labels = array_keys($data);
        $values = array_values($data);
        $colors = [
            'pending_approval' => '#fbbf24', // warning color
            'verified' => '#22c55e',        // success color
            'rejected' => '#ef4444',        // danger color
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Status Distribution',
                    'data' => $values,
                    'backgroundColor' => array_map(fn($status) => $colors[$status], $labels),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_map(fn($status) => ucwords(str_replace('_', ' ', $status)), $labels),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
