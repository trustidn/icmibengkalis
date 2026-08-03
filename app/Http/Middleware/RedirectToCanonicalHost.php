<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Domain asing yang DNS-nya menunjuk ke IP server ini (mis. domain bekas
 * pemilik IP sebelumnya) akan dilayani nginx apa adanya, lalu diindeks Google
 * sebagai situs duplikat dengan canonical yang salah. Redirect permanen semua
 * host selain host APP_URL agar sinyal SEO kembali ke domain resmi.
 */
class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($canonicalHost && $request->getHost() !== $canonicalHost) {
            return redirect()->away(
                rtrim((string) config('app.url'), '/').$request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}
