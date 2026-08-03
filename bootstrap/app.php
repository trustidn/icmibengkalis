<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ForceHttpsScheme;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/member.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'track.view' => TrackPageView::class,
        ]);

        $middleware->appendToGroup('web', EnsureUserIsActive::class);

        // Di produksi app berada di belakang proxy/CDN yang menangani TLS —
        // percayai header X-Forwarded-* agar Laravel tahu request aslinya HTTPS.
        $middleware->trustProxies(at: '*');

        // Jaring pengaman bila proxy mengirim X-Forwarded-Proto: http (mis. Cloudflare
        // "Flexible" / Nginx Proxy Manager tanpa TLS ke origin). WAJIB append — harus
        // berjalan SESUDAH TrustProxies, kalau tidak setelannya ditimpa kembali dan
        // URL bertanda tangan (unggahan Livewire) gagal validasi -> HTTP 401.
        $middleware->append(ForceHttpsScheme::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
