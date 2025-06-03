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
        // Link The Beatles to Tomorrowland concert
        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'The Beatles')->first()->id,
            'concert_id' => Concert::whereHas('event', function ($query) {
                $query->where('name', 'Tomorrowland');
            })->first()->id,
        ]);

        // Link The Rolling Stones to Graspop concert
        ArtistConcert::create([
            'artist_id' => Artist::where('name', 'Paleface Swiss')->first()->id,
            'concert_id' => Concert::whereHas('event', function ($query) {
                $query->where('name', 'Graspop Metal Meeting');
            })->first()->id,
        ]);
    }
}
