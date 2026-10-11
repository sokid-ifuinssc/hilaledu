<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        if (config('app.env') === 'production' || str_starts_with(config('app.url', ''), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Event Listener untuk mencatat login secara otomatis
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if ($event->user instanceof \App\Models\User) {
                app(\App\Services\LoginLogService::class)->logSuccess($event->user, request());
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            $input = $event->credentials['email'] ?? $event->credentials['username'] ?? 'unknown';
            $user = $event->user instanceof \App\Models\User ? $event->user : null;
            app(\App\Services\LoginLogService::class)->logFailed((string) $input, 'Percobaan login gagal', request(), $user);
        });
    }
}
