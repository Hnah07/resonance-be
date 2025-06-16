<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Artist;
use App\Models\Concert;
use App\Models\ArtistConcert;

class ArtistConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Day 1 - June 20
        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Iron Maiden')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-20')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);

        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Amenra')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-20')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);

        // Day 2 - June 21
        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Slipknot')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-21')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);

        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Behemoth')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-21')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);

        // Day 3 - June 22
        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Korn')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-22')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);

        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Alien Ant Farm')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-22')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);

        // Day 4 - June 23
        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Dream Theater')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-23')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);

        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Alestorm')->first()->id,
            'concert_id' => Concert::whereDate('date', '2024-06-23')
                ->whereHas('event', fn($q) => $q->where('name', 'Graspop Metal Meeting'))
                ->first()->id,
        ]);
        // ArtistConcert::create([
        //     'artist_id' => Artist::where('name', 'The Beatles')->first()->id,
        //     'concert_id' => Concert::whereHas('event', function ($query) {
        //         $query->where('name', 'Tomorrowland');
        //     })->first()->id,
        // ]);
        // ArtistConcert::create([
        //     'artist_id' => Artist::where('name', 'The Who')->first()->id,
        //     'concert_id' => Concert::whereHas('event', function ($query) {
        //         $query->where('name', 'Tomorrowland');
        //     })->first()->id,
        // ]);
        // ArtistConcert::create([
        //     'artist_id' => Artist::where('name', 'The Rolling Stones')->first()->id,
        //     'concert_id' => Concert::whereHas('event', function ($query) {
        //         $query->where('name', 'Graspop Metal Meeting');
        //     })->first()->id,
        // ]);
        // ArtistConcert::create([
        //     'artist_id' => Artist::where('name', 'The Doors')->first()->id,
        //     'concert_id' => Concert::whereHas('event', function ($query) {
        //         $query->where('name', 'Graspop Metal Meeting');
        //     })->first()->id,
        // ]);
        // // Link The Rolling Stones to Graspop concert
        // ArtistConcert::create([
        //     'artist_id' => Artist::where('name', 'Paleface Swiss')->first()->id,
        //     'concert_id' => Concert::whereHas('event', function ($query) {
        //         $query->where('name', 'Graspop Metal Meeting');
        //     })->first()->id,
        // ]);
    }
}
