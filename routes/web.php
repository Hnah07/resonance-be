<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Debug route for testing file uploads
Route::get('/debug-upload', function () {
    return response()->json([
        'message' => 'Upload endpoint is accessible',
        'session_id' => session()->getId(),
        'user' => Auth::user() ? Auth::user()->id : 'not authenticated',
        'csrf_token' => csrf_token(),
    ]);
})->middleware(['web']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
