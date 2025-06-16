<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Source;
use App\Models\Status;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
            'name' => 'Graspop Metal Meeting',
            'start_date' => '2024-06-20',
            'end_date' => '2024-06-23',
            'description' => 'Belgium\'s biggest metal festival, featuring top international and local artists.',
            'type' => 'festival',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Event::create([
            'name' => 'Tomorrowland',
            'start_date' => '2024-07-19',
            'end_date' => '2024-07-28',
            'description' => 'The world\'s biggest electronic dance music festival, featuring the best DJs from around the globe.',
            'type' => 'festival',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
    }
}
