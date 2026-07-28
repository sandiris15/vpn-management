<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Wajib ditambahkan di atas

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
        // Memaksa skema HTTPS dan mendeteksi reverse proxy Nginx
        if (request()->server('HTTP_X_FORWARDED_PROTO') == 'https' || app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
