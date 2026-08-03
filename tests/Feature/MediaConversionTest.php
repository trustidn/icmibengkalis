<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Poster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_gambar_utama_artikel_dikonversi_thumb_dan_large(): void
    {
        $post = Post::factory()->create();
        $file = UploadedFile::fake()->image('sampul.jpg', 2000, 1200);
        $post->addMedia($file->getRealPath())->usingFileName('sampul.jpg')->toMediaCollection('featured');

        $this->assertStringContainsString('large', (string) $post->featuredImageUrl());
        $this->assertStringContainsString('thumb', (string) $post->featuredThumbUrl());
        $this->assertStringEndsWith('.webp', (string) $post->featuredThumbUrl());
    }

    public function test_foto_anggota_dikonversi_thumb(): void
    {
        $member = Member::factory()->create();
        $file = UploadedFile::fake()->image('foto.jpg', 1200, 1600);
        $member->addMedia($file->getRealPath())->usingFileName('foto.jpg')->toMediaCollection('photo');

        $this->assertStringContainsString('thumb', (string) $member->photoUrl());
    }

    public function test_logo_partner_dan_poster_dan_halaman_statis_dikonversi(): void
    {
        $partner = Partner::factory()->create();
        $logo = UploadedFile::fake()->image('logo.png', 900, 400);
        $partner->addMedia($logo->getRealPath())->usingFileName('logo.png')->toMediaCollection('logo');
        $this->assertStringContainsString('thumb', (string) $partner->logoUrl());

        $poster = Poster::factory()->create();
        $posterImage = UploadedFile::fake()->image('poster.jpg', 2400, 1200);
        $poster->addMedia($posterImage->getRealPath())->usingFileName('poster.jpg')->toMediaCollection('image');
        $this->assertStringContainsString('large', (string) $poster->imageUrl());

        $page = Page::factory()->create();
        $pageImage = UploadedFile::fake()->image('sambutan.jpg', 2000, 2600);
        $page->addMedia($pageImage->getRealPath())->usingFileName('sambutan.jpg')->toMediaCollection('featured');
        $this->assertStringContainsString('large', (string) $page->featuredImageUrl());
    }

    public function test_media_lama_tanpa_konversi_jatuh_ke_file_asli(): void
    {
        $post = Post::factory()->create();
        $file = UploadedFile::fake()->image('sampul.jpg');
        $post->addMedia($file->getRealPath())->usingFileName('sampul.jpg')->toMediaCollection('featured');

        $media = $post->getFirstMedia('featured');
        $media->generated_conversions = [];
        $media->save();

        $url = (string) $post->fresh()->featuredImageUrl();
        $this->assertStringContainsString('sampul.jpg', $url);
        $this->assertStringNotContainsString('conversions', $url);
    }
}
