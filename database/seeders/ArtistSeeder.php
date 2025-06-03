<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Artist;
use App\Models\Country;
use App\Models\Source;
use App\Models\Status;

class ArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artist::create([
            'name' => 'The Beatles',
            'description' => 'The Beatles were an English rock band formed in Liverpool in 1960.',
            'country_id' => Country::where('code', 'GB')->first()->id,
            'formed_year' => '1960',
            'image_url' => 'https://via.placeholder.com/150',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'The Rolling Stones',
            'description' => 'The Rolling Stones were an English rock band formed in 1962.',
            'country_id' => Country::where('code', 'GB')->first()->id,
            'formed_year' => '1962',
            'image_url' => 'https://via.placeholder.com/150',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'The Who',
            'description' => 'The Who were an English rock band formed in 1964.',
            'country_id' => Country::where('code', 'GB')->first()->id,
            'formed_year' => '1964',
            'image_url' => 'https://via.placeholder.com/150',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'The Doors',
            'description' => 'The Doors were an American rock band formed in 1965.',
            'country_id' => Country::where('code', 'US')->first()->id,
            'formed_year' => '1965',
            'image_url' => 'https://via.placeholder.com/150',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'The Police',
            'description' => 'The Police were an English rock band formed in 1977.',
            'country_id' => Country::where('code', 'GB')->first()->id,
            'formed_year' => '1977',
            'image_url' => 'https://via.placeholder.com/150',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'Paleface Swiss',
            'description' => 'Paleface Swiss is Swiss deathcore band.',
            'country_id' => Country::where('code', 'CH')->first()->id,
            'formed_year' => '2017',
            'image_url' => 'https://via.placeholder.com/150',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
    }
}
