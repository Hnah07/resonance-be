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
            'name' => 'Iron Maiden',
            'description' => 'Legendary British heavy metal band.',
            'country_id' => Country::where('code', 'GB')->first()->id,
            'formed_year' => '1975',
            'image_url' => '', // handmatig later toevoegen
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);

        Artist::create([
            'name' => 'Slipknot',
            'description' => 'American heavy metal band from Iowa.',
            'country_id' => Country::where('code', 'US')->first()->id,
            'formed_year' => '1995',
            'image_url' => '',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);

        Artist::create([
            'name' => 'Korn',
            'description' => 'American nu metal band.',
            'country_id' => Country::where('code', 'US')->first()->id,
            'formed_year' => '1993',
            'image_url' => '',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);

        Artist::create([
            'name' => 'Dream Theater',
            'description' => 'American progressive metal band.',
            'country_id' => Country::where('code', 'US')->first()->id,
            'formed_year' => '1985',
            'image_url' => '',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'Alestorm',
            'description' => 'Pirate metal from Scotland.',
            'country_id' => Country::where('code', 'GB')->first()->id,
            'formed_year' => '2004',
            'image_url' => '',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'Alien Ant Farm',
            'description' => 'American rock band known for cover hits.',
            'country_id' => Country::where('code', 'US')->first()->id,
            'formed_year' => '1995',
            'image_url' => '',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'Amenra',
            'description' => 'Belgian post-metal band.',
            'country_id' => Country::where('code', 'BE')->first()->id,
            'formed_year' => '1999',
            'image_url' => '',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'Behemoth',
            'description' => 'Polish extreme metal band.',
            'country_id' => Country::where('code', 'PL')->first()->id,
            'formed_year' => '1991',
            'image_url' => '',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        Artist::create([
            'name' => 'Beast In Black',
            'description' => 'Finnish‑Greek heavy metal band.',
            'country_id' => Country::where('code', 'FI')->first()->id,
            'formed_year' => '2015',
            'image_url' => null,
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        // Artist::create([
        //     'name' => 'The Beatles',
        //     'description' => 'The Beatles were an English rock band formed in Liverpool in 1960.',
        //     'country_id' => Country::where('code', 'GB')->first()->id,
        //     'formed_year' => '1960',
        //     'image_url' => 'https://via.placeholder.com/150',
        //     'source_id' => Source::where('source', 'manual')->first()->id,
        //     'status_id' => Status::where('status', 'verified')->first()->id,
        // ]);
        // Artist::create([
        //     'name' => 'The Rolling Stones',
        //     'description' => 'The Rolling Stones were an English rock band formed in 1962.',
        //     'country_id' => Country::where('code', 'GB')->first()->id,
        //     'formed_year' => '1962',
        //     'image_url' => 'https://via.placeholder.com/150',
        //     'source_id' => Source::where('source', 'manual')->first()->id,
        //     'status_id' => Status::where('status', 'verified')->first()->id,
        // ]);
        // Artist::create([
        //     'name' => 'The Who',
        //     'description' => 'The Who were an English rock band formed in 1964.',
        //     'country_id' => Country::where('code', 'GB')->first()->id,
        //     'formed_year' => '1964',
        //     'image_url' => 'https://via.placeholder.com/150',
        //     'source_id' => Source::where('source', 'manual')->first()->id,
        //     'status_id' => Status::where('status', 'verified')->first()->id,
        // ]);
        // Artist::create([
        //     'name' => 'The Doors',
        //     'description' => 'The Doors were an American rock band formed in 1965.',
        //     'country_id' => Country::where('code', 'US')->first()->id,
        //     'formed_year' => '1965',
        //     'image_url' => 'https://via.placeholder.com/150',
        //     'source_id' => Source::where('source', 'manual')->first()->id,
        //     'status_id' => Status::where('status', 'verified')->first()->id,
        // ]);
        // Artist::create([
        //     'name' => 'The Police',
        //     'description' => 'The Police were an English rock band formed in 1977.',
        //     'country_id' => Country::where('code', 'GB')->first()->id,
        //     'formed_year' => '1977',
        //     'image_url' => 'https://via.placeholder.com/150',
        //     'source_id' => Source::where('source', 'manual')->first()->id,
        //     'status_id' => Status::where('status', 'verified')->first()->id,
        // ]);
        // Artist::create([
        //     'name' => 'Paleface Swiss',
        //     'description' => 'Paleface Swiss is Swiss deathcore band.',
        //     'country_id' => Country::where('code', 'CH')->first()->id,
        //     'formed_year' => '2017',
        //     'image_url' => 'https://via.placeholder.com/150',
        //     'source_id' => Source::where('source', 'manual')->first()->id,
        //     'status_id' => Status::where('status', 'verified')->first()->id,
        // ]);
    }
}
