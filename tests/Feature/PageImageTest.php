<?php

namespace Tests\Feature;

use App\Livewire\Admin\Pages\Editor;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PageImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_ganti_gambar_saat_media_lama_berkasnya_hilang(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $page = Page::factory()->create(['slug' => 'sambutan-ketua', 'title' => 'Sambutan Ketua']);

        // Media lama: record ada, berkas dihapus (persis kondisi server)
        $lama = UploadedFile::fake()->image('featured.png');
        $page->addMedia($lama->getRealPath())->usingFileName('featured.png')->toMediaCollection('featured');
        $mediaLama = $page->getFirstMedia('featured');
        @unlink($mediaLama->getPath());
        $this->assertFileDoesNotExist($mediaLama->getPath());

        Livewire::actingAs($admin)
            ->test(Editor::class)
            ->call('select', $page->id)
            ->set('featured_image', UploadedFile::fake()->image('baru.jpg', 800, 600))
            ->call('save')
            ->assertHasNoErrors();

        $page->refresh();
        $url = $page->featuredImageUrl();
        $this->assertNotNull($url, 'URL gambar harus ada');
        $media = $page->getFirstMedia('featured');
        $this->assertFileExists($media->getPath(), 'Berkas gambar baru harus benar-benar ada di disk');
        // Nama berkas sengaja dipatok 'featured.<ext>' oleh editor, bukan nama asli.
        $this->assertSame('featured.jpg', $media->file_name);
        $this->assertNotSame($mediaLama->id, $media->id, 'Media lama harus digantikan, bukan menumpuk.');
    }
}
