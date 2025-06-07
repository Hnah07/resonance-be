<?php

namespace Database\Seeders;

use App\Models\CheckinComment;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckinCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CheckinComment::create([
            'checkin_id' => Checkin::where('user_id', User::where('email', 'admin@r.be')->first()->id)->first()->id,
            'user_id' => User::where('email', 'test@example.com')->first()->id,
            'comment' => 'This is a test comment'
        ]);
    }
}
