<?php

namespace Tests\Unit;

use App\Support\VideoUrl;
use PHPUnit\Framework\TestCase;

class VideoUrlTest extends TestCase
{
    public function test_mengenali_url_youtube_watch(): void
    {
        $parsed = VideoUrl::parse('https://www.youtube.com/watch?v=YE7VzlLtp-4');

        $this->assertSame('youtube', $parsed['provider']);
        $this->assertSame('YE7VzlLtp-4', $parsed['id']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/YE7VzlLtp-4', $parsed['embed_url']);
        $this->assertSame('https://img.youtube.com/vi/YE7VzlLtp-4/hqdefault.jpg', $parsed['thumbnail_url']);
    }

    public function test_mengenali_url_youtu_be_pendek(): void
    {
        $parsed = VideoUrl::parse('https://youtu.be/YE7VzlLtp-4?si=abc123');

        $this->assertSame('youtube', $parsed['provider']);
        $this->assertSame('YE7VzlLtp-4', $parsed['id']);
    }

    public function test_mengenali_url_youtube_shorts(): void
    {
        $parsed = VideoUrl::parse('https://www.youtube.com/shorts/YE7VzlLtp-4');

        $this->assertSame('youtube', $parsed['provider']);
        $this->assertSame('YE7VzlLtp-4', $parsed['id']);
    }

    public function test_mengenali_url_vimeo(): void
    {
        $parsed = VideoUrl::parse('https://vimeo.com/394786363');

        $this->assertSame('vimeo', $parsed['provider']);
        $this->assertSame('394786363', $parsed['id']);
        $this->assertSame('https://player.vimeo.com/video/394786363', $parsed['embed_url']);
        $this->assertNull($parsed['thumbnail_url']);
    }

    public function test_url_tidak_dikenali_mengembalikan_null(): void
    {
        $this->assertNull(VideoUrl::parse('https://example.com/video.mp4'));
        $this->assertNull(VideoUrl::parse('bukan url sama sekali'));
    }
}
