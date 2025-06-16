<?php

namespace Database\Seeders;

use App\Models\Follower;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FollowerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Follower::create([
        //     'follower_id' => User::where('email', 'admin@r.be')->first()->id,
        //     'followed_id' => User::where('email', 'test@example.com')->first()->id,
        // ]);
    }
}
