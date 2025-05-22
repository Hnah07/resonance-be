<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Rock',
            'Pop',
            'Hip Hop',
            'Electronic',
            'Jazz',
            'Blues',
            'Classical',
            'Reggae',
            'Punk',
            'Metal',
            'Folk',
            'Soul',
            'Country',
            'Indie',
            'R&B',
            'Funk',
            'Disco',
            'House',
            'Techno',
            'K-Pop',
            'Rap',
            'Trance',
            'Dubstep',
            'Schlager',
            'ska',
            'Psytrance',
            'Drum and Bass',
        ];

        foreach ($genres as $genre) {
            Genre::create([
                'genre' => $genre,
            ]);
        }
    }
}
