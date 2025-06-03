<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\ArtistGenre;

class ArtistGenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'The Beatles')->first()->id,
            'genre_id' => Genre::where('genre', 'Rock')->first()->id,
        ]);
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Paleface Swiss')->first()->id,
            'genre_id' => Genre::where('genre', 'Deathcore')->first()->id,
        ]);
    }
}
