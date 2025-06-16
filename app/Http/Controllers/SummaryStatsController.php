<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SummaryStatsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/summary-stats",
     *     summary="Get summary statistics for the logged-in user",
     *     tags={"Summary Stats"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Summary statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="concerts_this_year", type="integer", example=12),
     *             @OA\Property(property="total_concerts", type="integer", example=45),
     *             @OA\Property(property="countries_visited", type="integer", example=5),
     *             @OA\Property(property="favorite_genre", type="object",
     *                 @OA\Property(property="genre", type="string", example="Rock"),
     *                 @OA\Property(property="count", type="integer", example=15)
     *             ),
     *             @OA\Property(property="most_seen_artist", type="object",
     *                 @OA\Property(property="name", type="string", example="Arctic Monkeys"),
     *                 @OA\Property(property="count", type="integer", example=3)
     *             ),
     *             @OA\Property(property="top_venue", type="object",
     *                 @OA\Property(property="name", type="string", example="Sportpaleis"),
     *                 @OA\Property(property="count", type="integer", example=8)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        $currentYear = Carbon::now()->year;

        // Concerts this year
        $concertsThisYear = Checkin::where('user_id', $userId)
            ->whereHas('concert', function ($query) use ($currentYear) {
                $query->whereYear('date', $currentYear);
            })
            ->count();

        // Total concerts attended
        $totalConcerts = Checkin::where('user_id', $userId)->count();

        // Countries visited
        $countriesVisited = Checkin::where('checkins.user_id', $userId)
            ->join('concerts', 'checkins.concert_id', '=', 'concerts.id')
            ->join('locations', 'concerts.location_id', '=', 'locations.id')
            ->join('countries', 'locations.country_id', '=', 'countries.id')
            ->select('countries.id', 'countries.name')
            ->distinct()
            ->get();

        $countriesCount = $countriesVisited->count();
        $countriesList = $countriesVisited->pluck('name')->toArray();

        // Favorite genre
        $favoriteGenre = Genre::select('genres.genre', DB::raw('COUNT(*) as count'))
            ->join('artist_genres', 'genres.id', '=', 'artist_genres.genre_id')
            ->join('artists', 'artist_genres.artist_id', '=', 'artists.id')
            ->join('artist_checkins', 'artists.id', '=', 'artist_checkins.artist_id')
            ->join('checkins', 'artist_checkins.checkin_id', '=', 'checkins.id')
            ->where('checkins.user_id', $userId)
            ->groupBy('genres.id', 'genres.genre')
            ->orderByDesc('count')
            ->first();

        // Most seen artist
        $mostSeenArtist = Artist::select('artists.name', DB::raw('COUNT(*) as count'))
            ->join('artist_checkins', 'artists.id', '=', 'artist_checkins.artist_id')
            ->join('checkins', 'artist_checkins.checkin_id', '=', 'checkins.id')
            ->where('checkins.user_id', $userId)
            ->groupBy('artists.id', 'artists.name')
            ->orderByDesc('count')
            ->first();

        // Top venue
        $topVenue = Location::select('locations.name', DB::raw('COUNT(*) as count'))
            ->join('concerts', 'locations.id', '=', 'concerts.location_id')
            ->join('checkins', 'concerts.id', '=', 'checkins.concert_id')
            ->where('checkins.user_id', $userId)
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('count')
            ->first();

        return response()->json([
            'concerts_this_year' => $concertsThisYear,
            'total_concerts' => $totalConcerts,
            'countries_visited' => $countriesCount,
            'countries_list' => $countriesList,
            'favorite_genre' => $favoriteGenre ? [
                'genre' => $favoriteGenre->genre,
                'count' => $favoriteGenre->count
            ] : null,
            'most_seen_artist' => $mostSeenArtist ? [
                'name' => $mostSeenArtist->name,
                'count' => $mostSeenArtist->count
            ] : null,
            'top_venue' => $topVenue ? [
                'name' => $topVenue->name,
                'count' => $topVenue->count
            ] : null
        ]);
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
}
