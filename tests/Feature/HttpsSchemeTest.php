<?php

namespace Tests\Feature;

use App\Http\Middleware\ForceHttpsScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HttpsSchemeTest extends TestCase
{
    private function lewatMiddleware(Request $request): Request
    {
        (new ForceHttpsScheme)->handle($request, fn ($r) => response('ok'));

        return $request;
    }

    public function test_request_http_dianggap_https_bila_app_url_https(): void
    {
        config(['app.url' => 'https://test.icmibengkalis.or.id']);

        $request = Request::create('http://test.icmibengkalis.or.id/apa-saja', 'GET');
        $this->assertFalse($request->isSecure());

        $this->lewatMiddleware($request);

        $this->assertTrue($request->isSecure(), 'Request harus dianggap https agar tanda tangan URL cocok.');
        $this->assertSame('https://test.icmibengkalis.or.id/apa-saja', $request->url());
    }

    public function test_tidak_memaksa_https_bila_app_url_http(): void
    {
        config(['app.url' => 'http://localhost']);

        $request = $this->lewatMiddleware(Request::create('http://localhost/apa-saja', 'GET'));

        $this->assertFalse($request->isSecure(), 'Dev lokal berbasis http tidak boleh terpengaruh.');
    }

    public function test_url_bertanda_tangan_tetap_valid_saat_proxy_meneruskan_http(): void
    {
        // Regresi: unggahan Livewire gagal 401 di server di belakang Cloudflare/NPM
        // karena tanda tangan dibuat https tetapi divalidasi sebagai http.
        config(['app.url' => 'https://test.icmibengkalis.or.id']);
        URL::forceScheme('https');

        $signed = URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5), []);
        $this->assertStringStartsWith('https://', $signed);

        // Proxy meneruskan ke origin sebagai http:
        $request = Request::create(str_replace('https://', 'http://', $signed), 'POST');
        $this->assertFalse($request->hasValidSignature(), 'Prakondisi: tanpa perbaikan memang gagal.');

        $this->lewatMiddleware($request);

        $this->assertTrue($request->hasValidSignature(), 'Setelah middleware, tanda tangan harus cocok kembali.');
    }
}
