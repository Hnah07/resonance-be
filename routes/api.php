<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ArtistController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('events', EventController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('locations', LocationController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::apiResource('artists', ArtistController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
