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
        // Iron Maiden – Heavy Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Iron Maiden')->first()->id,
            'genre_id' => Genre::where('genre', 'Heavy Metal')->first()->id,
        ]);

        // Slipknot – Nu Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Slipknot')->first()->id,
            'genre_id' => Genre::where('genre', 'Nu Metal')->first()->id,
        ]);

        // Korn – Nu Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Korn')->first()->id,
            'genre_id' => Genre::where('genre', 'Nu Metal')->first()->id,
        ]);

        // Amenra – Post-Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Amenra')->first()->id,
            'genre_id' => Genre::where('genre', 'Post-Metal')->first()->id,
        ]);

        // Dream Theater – Progressive Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Dream Theater')->first()->id,
            'genre_id' => Genre::where('genre', 'Progressive Metal')->first()->id,
        ]);

        // Alestorm – Pirate Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Alestorm')->first()->id,
            'genre_id' => Genre::where('genre', 'Pirate Metal')->first()->id,
        ]);

        // Alien Ant Farm – Alternative Rock
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Alien Ant Farm')->first()->id,
            'genre_id' => Genre::where('genre', 'Alternative Rock')->first()->id,
        ]);

        // Behemoth – Blackened Death Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Behemoth')->first()->id,
            'genre_id' => Genre::where('genre', 'Blackened Death Metal')->first()->id,
        ]);

        // Beast In Black – Heavy Metal
        ArtistGenre::create([
            'artist_id' => Artist::where('name', 'Beast In Black')->first()->id,
            'genre_id' => Genre::where('genre', 'Heavy Metal')->first()->id,
        ]);
    }
}
