<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Bila APP_URL memakai https, paksa seluruh URL yang dihasilkan (asset(),
        // url(), script Livewire) ber-skema https — mencegah mixed content saat
        // TLS ditangani proxy/CDN di depan container (request internal tetap http).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
