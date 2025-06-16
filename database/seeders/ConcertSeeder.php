<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Concert;
use App\Models\Event;
use App\Models\Location;
use App\Models\Source;
use App\Models\Status;

class ConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Concert::create([
            'event_id' => Event::where('name', 'Tomorrowland')->first()->id,
            'location_id' => Location::where('name', 'Provinciaal Recreatiedomein De Schorre')->first()->id,
            'date' => '2025-07-20',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Concert::create([
            'event_id' => Event::where('name', 'Graspop Metal Meeting')->first()->id,
            'location_id' => Location::where('name', 'Festivalpark Stenehei')->first()->id,
            'date' => '2024-06-19',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);

        Concert::create([
            'event_id' => Event::where('name', 'Graspop Metal Meeting')->first()->id,
            'location_id' => Location::where('name', 'Festivalpark Stenehei')->first()->id,
            'date' => '2024-06-20',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);

        Concert::create([
            'event_id' => Event::where('name', 'Graspop Metal Meeting')->first()->id,
            'location_id' => Location::where('name', 'Festivalpark Stenehei')->first()->id,
            'date' => '2024-06-21',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);

        Concert::create([
            'event_id' => Event::where('name', 'Graspop Metal Meeting')->first()->id,
            'location_id' => Location::where('name', 'Festivalpark Stenehei')->first()->id,
            'date' => '2024-06-22',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
    }
}
