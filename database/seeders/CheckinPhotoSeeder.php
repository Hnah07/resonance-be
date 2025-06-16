<?php

namespace Database\Seeders;

use App\Models\CheckinPhoto;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckinPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CheckinPhoto::create([
        //     'checkin_id' => Checkin::where('user_id', User::where('email', 'admin@r.be')->first()->id)->first()->id,
        //     'url' => 'https://placehold.co/600x400',
        //     'caption' => 'This is a test caption'
        // ]);
    }
}
