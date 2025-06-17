<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\ConcertController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\CheckinPhotoController;
use App\Http\Controllers\CheckinCommentController;
use App\Http\Controllers\CheckinLikeController;
use App\Http\Controllers\CheckinRatingController;
use App\Http\Controllers\CheckinReviewController;
use App\Http\Controllers\ArtistCheckinController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\SummaryStatsController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public user routes
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/search', [UserController::class, 'search']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/users/{id}/check-ins', [UserController::class, 'checkins']);
Route::get('/users/{userId}/summary-stats', [UserController::class, 'summaryStats']);
Route::get('/users/{id}/followers', [FollowerController::class, 'getFollowers']);
Route::get('/users/{id}/following', [FollowerController::class, 'getFollowing']);

// Protected routes that require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/profile/stats', [StatsController::class, 'profileStats']);
    Route::post('/upload', [FileUploadController::class, 'store']);
    Route::get('/timeline', [TimelineController::class, 'index']);
    Route::get('/summary-stats', [SummaryStatsController::class, 'index']);

    // Follow/unfollow actions
    Route::post('/followers', [FollowerController::class, 'store']);
    Route::delete('/followers/{id}', [FollowerController::class, 'destroy']);
    Route::post('/users/{user}/follow', [UserController::class, 'follow']);
    Route::delete('/users/{user}/follow', [UserController::class, 'unfollow']);

    // Other authenticated routes
    Route::apiResource('checkins', CheckinController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::apiResource('checkin-photos', CheckinPhotoController::class);
    Route::apiResource('checkin-comments', CheckinCommentController::class);
    Route::apiResource('checkin-likes', CheckinLikeController::class);
    Route::apiResource('checkin-ratings', CheckinRatingController::class);
    Route::apiResource('checkin-reviews', CheckinReviewController::class);
    Route::apiResource('artist-checkins', ArtistCheckinController::class);

    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/countries/{country}', [CountryController::class, 'show']);
});

Route::middleware('api')->group(function () {
    Route::apiResource('events', EventController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('locations', LocationController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('artists', ArtistController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('concerts', ConcertController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('genres', GenreController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('followers', FollowerController::class)->only(['index', 'store', 'destroy']);

    // // Concert artist management routes
    // Route::get('/concerts/{concert}/artists', [ConcertController::class, 'getArtists']);
    // Route::post('/concerts/{concert}/artists', [ConcertController::class, 'attachArtist']);
    // Route::delete('/concerts/{concert}/artists/{artistId}', [ConcertController::class, 'detachArtist']);

    // // Artist concert management routes
    // Route::get('/artists/{artist}/concerts', [ArtistController::class, 'getConcerts']);
    // Route::post('/artists/{artist}/concerts', [ArtistController::class, 'attachConcert']);
    // Route::delete('/artists/{artist}/concerts/{concertId}', [ArtistController::class, 'detachConcert']);

    // // Artist genre management routes
    // Route::get('/artists/{artist}/genres', [ArtistController::class, 'getGenres']);
    // Route::post('/artists/{artist}/genres', [ArtistController::class, 'attachGenres']);
    // Route::delete('/artists/{artist}/genres/{genreId}', [ArtistController::class, 'detachGenre']);

    Route::apiResource('concerts.artists', ConcertController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('artists.concerts', ArtistController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('artists.genres', ArtistController::class)->only(['index', 'store', 'update', 'destroy']);
});
