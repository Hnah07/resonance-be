<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Routing\UrlGenerator;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        if (env("APP_ENV") === "production") {
            URL::forceScheme('https');
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Ensure Livewire upload routes use proper session handling
        Route::matched(function ($route) {
            if (str_starts_with($route->uri(), 'livewire/upload-file')) {
                // Ensure session is started for Livewire uploads
                if (!session()->isStarted()) {
                    session()->start();
                }

                // Check authentication
                if (!Auth::check()) {
                    abort(401, 'Unauthorized');
                }
            }
        });
    }
}
