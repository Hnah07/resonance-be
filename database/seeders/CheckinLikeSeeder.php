<?php

namespace Database\Seeders;

use App\Models\CheckinLike;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckinLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CheckinLike::create([
            'checkin_id' => Checkin::where('user_id', User::where('email', 'admin@r.be')->first()->id)->first()->id,
            'user_id' => User::where('email', 'test@example.com')->first()->id,
        ]);
    }
}
