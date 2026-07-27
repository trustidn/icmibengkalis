<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pendaftaran tidak ditautkan dari UI publik (keanggotaan tertutup), tetapi
 * route-nya tetap bisa diakses langsung sampai super admin menonaktifkannya
 * lewat Konfigurasi Web.
 */
class EnsureRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(SiteSetting::current()->registration_enabled, 404);

        return $next($request);
    }
}
