<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saat APP_URL https tetapi request tiba sebagai http (proxy/CDN di depan
 * container meneruskan tanpa X-Forwarded-Proto — mis. Cloudflare "Flexible"
 * atau Nginx Proxy Manager tanpa header proto), Laravel menganggap request
 * tidak aman. Akibatnya URL bertanda tangan yang DIBUAT sebagai https gagal
 * DIVALIDASI sebagai http -> Livewire menolak unggahan dengan HTTP 401.
 *
 * Middleware ini menyelaraskan keduanya: bila APP_URL https, request selalu
 * dianggap https. Bila proxy sudah mengirim header proto dengan benar,
 * middleware ini tidak melakukan apa-apa.
 */
class ForceHttpsScheme
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! str_starts_with((string) config('app.url'), 'https://')) {
            return $next($request);
        }

        // WAJIB menimpa header X-Forwarded-Proto, bukan cuma server var HTTPS:
        // bila proxy tepercaya mengirim "http" (Cloudflare Flexible / NPM tanpa TLS
        // ke origin), Symfony memprioritaskan header itu dan mengabaikan HTTPS=on.
        // Middleware ini juga WAJIB berjalan SESUDAH TrustProxies (lihat bootstrap/app.php).
        $request->headers->set('X-Forwarded-Proto', 'https');
        $request->server->set('HTTPS', 'on');

        return $next($request);
    }
}
