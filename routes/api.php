<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\ConcertController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('events', EventController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('locations', LocationController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('artists', ArtistController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('concerts', ConcertController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

// Concert artist management routes
Route::get('/concerts/{concert}/artists', [ConcertController::class, 'getArtists']);
Route::post('/concerts/{concert}/artists', [ConcertController::class, 'attachArtist']);
Route::delete('/concerts/{concert}/artists/{artistId}', [ConcertController::class, 'detachArtist']);

// Artist concert management routes
Route::get('/artists/{artist}/concerts', [ArtistController::class, 'getConcerts']);
Route::post('/artists/{artist}/concerts', [ArtistController::class, 'attachConcert']);
Route::delete('/artists/{artist}/concerts/{concertId}', [ArtistController::class, 'detachConcert']);
