<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('welcome');
});

// Route to serve storage files (fallback for production)
Route::get('/storage/{path}', function ($path) {
    // Remove any leading slashes
    $path = ltrim($path, '/');

    // Check multiple possible locations
    $possiblePaths = [
        storage_path('app/public/' . $path),
        storage_path('app/' . $path),
        storage_path($path),
    ];

    $fullPath = null;
    foreach ($possiblePaths as $testPath) {
        if (file_exists($testPath)) {
            $fullPath = $testPath;
            break;
        }
    }

    if (!$fullPath) {
        // Log the attempted access for debugging
        Log::info('File not found', [
            'requested_path' => $path,
            'possible_paths' => $possiblePaths,
            'storage_exists' => is_dir(storage_path('app/public')),
            'events_dir_exists' => is_dir(storage_path('app/public/events')),
        ]);
        abort(404, 'File not found: ' . $path);
    }

    $file = file_get_contents($fullPath);
    $type = mime_content_type($fullPath);

    return response($file, 200, [
        'Content-Type' => $type,
        'Cache-Control' => 'public, max-age=31536000',
        'Content-Length' => filesize($fullPath),
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

// Simple test route to check if file exists
Route::get('/test-file', function () {
    $testPath = 'events/gmm2025.png';
    $fullPath = storage_path('app/public/' . $testPath);

    return response()->json([
        'file_path' => $testPath,
        'full_path' => $fullPath,
        'exists' => file_exists($fullPath),
        'is_file' => is_file($fullPath),
        'size' => file_exists($fullPath) ? filesize($fullPath) : 'N/A',
        'storage_dir_exists' => is_dir(storage_path('app/public')),
        'events_dir_exists' => is_dir(storage_path('app/public/events')),
        'files_in_events' => is_dir(storage_path('app/public/events')) ? scandir(storage_path('app/public/events')) : 'N/A',
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
