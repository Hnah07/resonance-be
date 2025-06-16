<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Checkin;
use App\Models\Concert;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Location;
use App\Models\Follower;
use App\Models\User;

class StatsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get detailed statistics for the authenticated user's concert activity
     */
    public function profileStats(Request $request)
    {
        $user = $request->user();

        // Get followers (users that follow the authenticated user)
        $followers = User::select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->join('followers', 'users.id', '=', 'followers.follower_id')
            ->where('followers.followed_id', $user->id)
            ->get();

        // Get following (users that the authenticated user follows)
        $following = User::select('users.id', 'users.name', 'users.username', 'users.profile_photo_path')
            ->join('followers', 'users.id', '=', 'followers.followed_id')
            ->where('followers.follower_id', $user->id)
            ->get();

        // Monthly concert attendance based on concert dates
        $monthlyAttendance = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->select(
                DB::raw('MONTH(concerts.date) as month_number'),
                DB::raw('DATE_FORMAT(concerts.date, "%b") as month'),
                DB::raw('COUNT(DISTINCT checkins.id) as count')
            )
            ->groupBy('month_number', 'month')
            ->orderBy('month_number')
            ->get();

        // Genre distribution
        $genreDistribution = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('artist_concerts', 'concerts.id', '=', 'artist_concerts.concert_id')
            ->join('artists', 'artist_concerts.artist_id', '=', 'artists.id')
            ->join('artist_genres', 'artists.id', '=', 'artist_genres.artist_id')
            ->join('genres', 'artist_genres.genre_id', '=', 'genres.id')
            ->select('genres.genre as genre', DB::raw('COUNT(DISTINCT checkins.id) as count'))
            ->groupBy('genres.id', 'genres.genre')
            ->orderByDesc('count')
            ->get();

        // Top venues
        $topVenues = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('locations', 'concerts.location_id', '=', 'locations.id')
            ->select('locations.name as venue', DB::raw('COUNT(DISTINCT checkins.id) as count'))
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top artists
        $topArtists = Checkin::where('user_id', $user->id)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('artist_concerts', 'concerts.id', '=', 'artist_concerts.concert_id')
            ->join('artists', 'artist_concerts.artist_id', '=', 'artists.id')
            ->select(
                'artists.name as artist',
                'artists.image_url as image',
                DB::raw('COUNT(DISTINCT checkins.id) as count')
            )
            ->groupBy('artists.id', 'artists.name', 'artists.image_url')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'followers_count' => $followers->count(),
            'following_count' => $following->count(),
            'followers' => $followers,
            'following' => $following,
            'monthly_attendance' => $monthlyAttendance,
            'genre_distribution' => $genreDistribution,
            'top_venues' => $topVenues,
            'top_artists' => $topArtists
        ]);
    }
}
