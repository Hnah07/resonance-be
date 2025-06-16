<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\CheckinReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class CheckinReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CheckinReview::create([
        //     'checkin_id' => Checkin::where('user_id', User::where('email', 'admin@r.be')->first()->id)->first()->id,
        //     'review' => 'This is a review',
        // ]);
    }
}
