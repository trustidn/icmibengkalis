<?php

namespace Tests\Feature;

use App\Livewire\Admin\Gallery\Form;
use App\Models\Album;
use App\Models\AlbumItem;
use App\Models\User;
use App\Services\Gallery\GalleryService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_publik_hanya_melihat_album_yang_tayang(): void
    {
        Album::factory()->create(['title' => 'Album Tayang', 'is_published' => true]);
        Album::factory()->create(['title' => 'Album Draf', 'is_published' => false]);

        $this->get(route('gallery.index'))
            ->assertOk()
            ->assertSee('Album Tayang')
            ->assertDontSee('Album Draf');
    }

    public function test_album_belum_tayang_404_di_halaman_publik(): void
    {
        $album = Album::factory()->create(['is_published' => false]);

        $this->get(route('gallery.show', $album->slug))->assertNotFound();
    }

    public function test_admin_dengan_permission_bisa_upload_foto(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->givePermissionTo('gallery.manage');
        $album = Album::factory()->create(['type' => 'foto', 'created_by' => $admin->id]);

        $this->actingAs($admin);

        Livewire::actingAs($admin)
            ->test(Form::class, ['album' => $album])
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->call('addPhoto');

        $this->assertSame(1, $album->items()->count());
    }

    public function test_user_tanpa_permission_ditolak_akses_admin_gallery(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.gallery.index'))
            ->assertForbidden();
    }

    public function test_addphotofromurl_gagal_untuk_url_tidak_terjangkau(): void
    {
        $album = Album::factory()->create();

        $this->expectException(\RuntimeException::class);

        app(GalleryService::class)->addPhotoFromUrl($album, 'https://example.invalid/tidak-ada.jpg');
    }

    public function test_admin_melihat_pesan_error_saat_url_foto_gagal_diunduh(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('gallery.manage');
        $album = Album::factory()->create(['type' => 'foto', 'created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Form::class, ['album' => $album])
            ->set('photoMode', 'url')
            ->set('photoUrl', 'https://example.invalid/tidak-ada.jpg')
            ->call('addPhoto')
            ->assertHasErrors('photoUrl');

        $this->assertSame(0, $album->items()->count());
    }

    public function test_addvideo_youtube_tidak_perlu_panggilan_http(): void
    {
        Http::fake();

        $album = Album::factory()->create();

        $item = app(GalleryService::class)->addVideo($album, 'https://www.youtube.com/watch?v=YE7VzlLtp-4', 'Contoh video');

        $this->assertSame('youtube', $item->video_provider);
        $this->assertSame('https://img.youtube.com/vi/YE7VzlLtp-4/hqdefault.jpg', $item->thumbnail_url);
        Http::assertNothingSent();
    }

    public function test_addvideo_vimeo_mengambil_thumbnail_via_oembed(): void
    {
        Http::fake([
            'vimeo.com/api/oembed.json*' => Http::response(['thumbnail_url' => 'https://i.vimeocdn.com/video/thumb.jpg']),
        ]);

        $album = Album::factory()->create();

        $item = app(GalleryService::class)->addVideo($album, 'https://vimeo.com/394786363');

        $this->assertSame('vimeo', $item->video_provider);
        $this->assertSame('https://i.vimeocdn.com/video/thumb.jpg', $item->thumbnail_url);
    }

    public function test_addvideo_vimeo_thumbnail_null_bila_oembed_gagal(): void
    {
        Http::fake([
            'vimeo.com/api/oembed.json*' => Http::response([], 404),
        ]);

        $album = Album::factory()->create();

        $item = app(GalleryService::class)->addVideo($album, 'https://vimeo.com/394786363');

        $this->assertNull($item->thumbnail_url);
    }

    public function test_addvideo_url_tidak_dikenali_melempar_exception(): void
    {
        $album = Album::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        app(GalleryService::class)->addVideo($album, 'https://example.com/video.mp4');
    }

    public function test_admin_melihat_pesan_error_saat_url_video_tidak_dikenali(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('gallery.manage');
        $album = Album::factory()->create(['type' => 'video', 'created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(Form::class, ['album' => $album])
            ->set('videoUrl', 'https://example.com/video.mp4')
            ->call('addVideo')
            ->assertHasErrors('videoUrl');

        $this->assertSame(0, $album->items()->count());
    }

    public function test_latestitems_hanya_dari_album_tayang_dan_dibatasi_limit(): void
    {
        $published = Album::factory()->create(['is_published' => true]);
        $draft = Album::factory()->create(['is_published' => false]);

        AlbumItem::factory()->for($published)->count(3)->create();
        AlbumItem::factory()->for($draft)->count(2)->create();

        $items = app(GalleryService::class)->latestItems(2);

        $this->assertCount(2, $items);
        $this->assertTrue($items->every(fn (AlbumItem $item) => $item->album->is_published));
    }

    public function test_album_item_isvideo_dan_thumbnail_helper(): void
    {
        $photoItem = AlbumItem::factory()->create();
        $videoItem = AlbumItem::factory()->create([
            'video_url' => 'https://www.youtube.com/watch?v=YE7VzlLtp-4',
            'video_provider' => 'youtube',
            'thumbnail_url' => 'https://img.youtube.com/vi/YE7VzlLtp-4/hqdefault.jpg',
        ]);

        $this->assertFalse($photoItem->isVideo());
        $this->assertNull($photoItem->thumbnailUrl());
        $this->assertNull($photoItem->embedUrl());

        $this->assertTrue($videoItem->isVideo());
        $this->assertSame('https://img.youtube.com/vi/YE7VzlLtp-4/hqdefault.jpg', $videoItem->thumbnailUrl());
        $this->assertSame('https://www.youtube-nocookie.com/embed/YE7VzlLtp-4', $videoItem->embedUrl());
    }
}
