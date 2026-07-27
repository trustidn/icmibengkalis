<?php

namespace Tests\Feature;

use App\Livewire\Admin\Publishing\Form as PublishingForm;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EditorContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_bisa_mengunggah_gambar_editor(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->givePermissionTo('publishing.create');

        $response = $this->actingAs($admin)->post('/editor/upload', [
            'image' => UploadedFile::fake()->image('foto.jpg', 800, 600),
        ]);

        $response->assertOk()->assertJsonStructure(['url']);
    }

    public function test_pengguna_tanpa_hak_menulis_tidak_bisa_mengunggah(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/editor/upload', [
            'image' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertForbidden();
    }

    public function test_guest_tidak_bisa_mengakses_endpoint_unggah(): void
    {
        $this->post('/editor/upload')->assertRedirect('/login');
    }

    public function test_gambar_utama_tersimpan_saat_menyimpan_post(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['publishing.view', 'publishing.create', 'publishing.update']);

        Livewire::actingAs($admin)
            ->test(PublishingForm::class)
            ->set('title', 'Berita Bergambar')
            ->set('body', '<p>Isi berita.</p>')
            ->set('featured_image', UploadedFile::fake()->image('cover.jpg', 1200, 630))
            ->call('save')
            ->assertHasNoErrors();

        $post = \App\Models\Post::where('title', 'Berita Bergambar')->first();

        $this->assertNotNull($post);
        $this->assertNotNull($post->featuredImageUrl());
    }

    public function test_body_html_dirender_bersih_di_halaman_publik(): void
    {
        $page = Page::factory()->create([
            'slug' => 'tentang',
            'body' => '<p>Paragraf <strong>aman</strong>.</p><script>alert("xss")</script>',
        ]);

        $this->get('/tentang')
            ->assertOk()
            ->assertSee('aman')
            ->assertDontSee('alert("xss")', false);
    }

    public function test_beranda_menampilkan_seksi_sambutan_ketua(): void
    {
        Page::factory()->create([
            'slug' => 'sambutan-ketua',
            'title' => 'Sambutan Ketua',
            'body' => 'Assalamualaikum, selamat datang di portal ICMI Bengkalis.',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Baca Selengkapnya')
            ->assertSee('selamat datang di portal');
    }

    public function test_register_tetap_bisa_diakses_saat_aktif(): void
    {
        $this->get('/register')->assertOk();
    }

    public function test_register_404_saat_dinonaktifkan_dari_pengaturan(): void
    {
        SiteSetting::current()->update(['registration_enabled' => false]);
        SiteSetting::forgetCurrent();

        $this->get('/register')->assertNotFound();
    }
}
