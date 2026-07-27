<?php

namespace Tests\Unit;

use App\Support\ExternalImageUrl;
use PHPUnit\Framework\TestCase;

class ExternalImageUrlTest extends TestCase
{
    public function test_normalisasi_google_drive_file_view(): void
    {
        $url = ExternalImageUrl::normalize('https://drive.google.com/file/d/1AbCdEfGhIjK/view?usp=sharing');

        $this->assertSame('https://drive.google.com/uc?export=view&id=1AbCdEfGhIjK', $url);
    }

    public function test_normalisasi_google_drive_open_id(): void
    {
        $url = ExternalImageUrl::normalize('https://drive.google.com/open?id=1AbCdEfGhIjK');

        $this->assertSame('https://drive.google.com/uc?export=view&id=1AbCdEfGhIjK', $url);
    }

    public function test_normalisasi_dropbox_dl0_menjadi_raw(): void
    {
        $url = ExternalImageUrl::normalize('https://www.dropbox.com/s/abc123/foto.jpg?dl=0');

        $this->assertStringContainsString('raw=1', $url);
        $this->assertStringNotContainsString('dl=0', $url);
    }

    public function test_url_biasa_dikembalikan_apa_adanya(): void
    {
        $url = 'https://example.com/foto.jpg';

        $this->assertSame($url, ExternalImageUrl::normalize($url));
    }
}
