<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Debug routes for testing file uploads
Route::get('/debug-upload', function () {
    return response()->json([
        'message' => 'Upload endpoint is accessible',
        'session_id' => session()->getId(),
        'user' => Auth::user() ? Auth::user()->id : 'not authenticated',
        'csrf_token' => csrf_token(),
        'app_env' => config('app.env'),
        'app_url' => config('app.url'),
    ]);
})->middleware(['web']);

Route::get('/debug-session', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user_email' => Auth::user() ? Auth::user()->email : null,
        'cookies' => request()->cookies->all(),
    ]);
})->middleware(['web']);

// Test Livewire upload endpoint specifically
Route::post('/debug-livewire-upload', function () {
    return response()->json([
        'message' => 'Livewire upload endpoint test',
        'session_id' => session()->getId(),
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'method' => request()->method(),
        'headers' => request()->headers->all(),
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
