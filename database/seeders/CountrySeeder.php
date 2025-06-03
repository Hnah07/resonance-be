<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $files = File::files(base_path('vendor/rinvex/countries/resources/data'));

        foreach ($files as $file) {
            $json = File::get($file);
            $data = json_decode($json, true);

            if (empty($data['iso_3166_1_alpha2'])) {
                continue;
            }

            Country::create([
                'code' => $data['iso_3166_1_alpha2'] ?? null,
                'name' => $data['name']['common'] ?? null,
                'official_name' => $data['name']['official'] ?? null,
                'native_name' => collect($data['name']['native'] ?? [])->first()['common'] ?? null,
                'continent' => collect($data['geo']['continent'] ?? [])->keys()->first() ?? null,
                'subregion' => $data['geo']['subregion'] ?? null,
                'emoji' => $data['extra']['emoji'] ?? null,
                'latitude' => $data['geo']['latitude_desc'] ?? null,
                'longitude' => $data['geo']['longitude_desc'] ?? null,
            ]);
        }
    }
}
