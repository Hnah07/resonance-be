<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\Concert;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Checkin::create([
            'concert_id' => Concert::where('date', '2024-07-19')
                ->whereHas('location', function ($query) {
                    $query->where('name', 'Festivalpark Stenehei');
                })
                ->first()->id,
            'user_id' => User::where('email', 'admin@r.be')->first()->id,
        ]);
    }
}
