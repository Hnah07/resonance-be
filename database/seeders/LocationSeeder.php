<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Country;
use App\Models\Source;
use App\Models\Status;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $location = Location::create([
            'name' => 'Trix',
            'street' => 'Noordersingel',
            'house_number' => '28/30',
            'postal_code' => '2140',
            'city' => 'Antwerpen',
            'country_id' => Country::where('name', 'Belgium')->first()->id,
            'latitude' => '51.2238',
            'longitude' => '4.4034',
            'website' => 'https://www.trix.be',
            'description' => 'Trix is a concert venue in Antwerpen, Belgium.',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        $location = Location::create([
            'name' => 'Festivalpark Stenehei',
            'street' => 'Kastelsedijk',
            'house_number' => '5',
            'postal_code' => '2140',
            'city' => 'Dessel',
            'country_id' => Country::where('name', 'Belgium')->first()->id,
            'latitude' => '51.2238',
            'longitude' => '4.4034',
            'website' => 'https://www.festivalpark.be',
            'description' => 'Festivalpark Stenehei is a festival location in Dessel, Belgium.',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
        $location = Location::create([
            'name' => 'Provinciaal Recreatiedomein De Schorre',
            'street' => 'Schommelei',
            'house_number' => '1',
            'postal_code' => '2850',
            'city' => 'Boom',
            'country_id' => Country::where('name', 'Belgium')->first()->id,
            'latitude' => '51.0889',
            'longitude' => '4.3667',
            'website' => 'https://www.schommelei.be',
            'description' => 'Provinciaal Recreatiedomein De Schorre is a festival location in Boom, Belgium.',
            'source_id' => Source::where('source', 'manual')->first()->id,
            'status_id' => Status::where('status', 'verified')->first()->id,
        ]);
    }
}
