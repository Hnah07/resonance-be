<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\ArtistCheckin;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArtistCheckinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        ArtistCheckin::create([
            'artist_id' => Artist::where('name', 'Paleface Swiss')->first()->id,
            'checkin_id' => Checkin::where('user_id', User::where('email', 'admin@r.be')->first()->id)->first()->id,
        ]);
    }
}
