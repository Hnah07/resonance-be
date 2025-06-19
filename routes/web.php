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

// Custom Livewire upload route with proper session handling
Route::post('/livewire/upload-file', function () {
    // Ensure session is started
    if (!session()->isStarted()) {
        session()->start();
    }

    // Check authentication
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Handle the file upload
    $file = request()->file('file');
    if (!$file) {
        return response()->json(['error' => 'No file provided'], 400);
    }

    // Store the file temporarily
    $path = $file->store('livewire-tmp', 'public');

    return response()->json([
        'success' => true,
        'path' => $path,
        'filename' => $file->getClientOriginalName(),
        'session_id' => session()->getId(),
        'user_id' => Auth::id(),
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
