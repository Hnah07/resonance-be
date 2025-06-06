<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        // Seed in order of dependencies
        $this->call([
            CountrySeeder::class,      // First, as it's independent
            SourceSeeder::class,       // Independent
            StatusSeeder::class,       // Independent
            GenreSeeder::class,        // Independent
            ArtistSeeder::class,       // Depends on Country, Source, Status
            LocationSeeder::class,     // Depends on Country, Source, Status
            EventSeeder::class,        // Depends on Source, Status
            ConcertSeeder::class,      // Depends on Event, Location, Source, Status
            ArtistConcertSeeder::class, // Depends on Artist, Concert
            ArtistGenreSeeder::class, // Depends on Artist, Genre
            FollowerSeeder::class,     // Depends on User
            CheckinSeeder::class,      // Depends on User, Concert
            ArtistCheckinSeeder::class, // Depends on Artist, Checkin
            CheckinPhotoSeeder::class, // Depends on Checkin
        ]);
    }
}
