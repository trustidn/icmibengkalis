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
        if (! $request->isSecure() && str_starts_with((string) config('app.url'), 'https://')) {
            $request->server->set('HTTPS', 'on');
        }

        return $next($request);
    }
}
