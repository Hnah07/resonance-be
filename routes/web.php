<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Route to serve storage files (fallback for production)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    $file = file_get_contents($fullPath);
    $type = mime_content_type($fullPath);

    return response($file, 200, [
        'Content-Type' => $type,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');

// Debug route for production file issues
Route::get('/debug-files', function () {
    $events = \App\Models\Event::whereNotNull('image_url')->get();
    $fileInfo = [];

    foreach ($events as $event) {
        $dbPath = $event->image_url;
        $fullPath = storage_path('app/public/' . $dbPath);
        $exists = file_exists($fullPath);
        $publicUrl = asset('storage/' . $dbPath);

        $fileInfo[] = [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'db_image_url' => $dbPath,
            'full_storage_path' => $fullPath,
            'file_exists' => $exists,
            'public_url' => $publicUrl,
            'file_size' => $exists ? filesize($fullPath) : 'N/A',
        ];
    }

    // Also check what files actually exist in storage
    $storageFiles = [];
    $eventsDir = storage_path('app/public/events');
    if (is_dir($eventsDir)) {
        $files = scandir($eventsDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $storageFiles[] = [
                    'filename' => $file,
                    'full_path' => $eventsDir . '/' . $file,
                    'size' => filesize($eventsDir . '/' . $file),
                ];
            }
        }
    }

    return response()->json([
        'events_with_images' => $fileInfo,
        'files_in_storage' => $storageFiles,
        'storage_link_exists' => is_link(public_path('storage')),
        'storage_link_target' => is_link(public_path('storage')) ? readlink(public_path('storage')) : 'N/A',
        'app_url' => config('app.url'),
        'asset_url' => asset('storage/events/gmm2025.png'),
    ]);
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
