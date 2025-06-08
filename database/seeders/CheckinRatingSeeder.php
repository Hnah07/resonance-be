<?php

namespace Database\Seeders;

use App\Models\CheckinRating;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckinRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CheckinRating::create([
            'checkin_id' => Checkin::where('user_id', User::where('email', 'admin@r.be')->first()->id)->first()->id,
            'rating' => 3.5,
        ]);
    }
}
