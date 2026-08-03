<?php

namespace Tests\Feature;

use App\Livewire\Admin\Posters\Index;
use App\Models\Poster;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PosterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function adminWeb(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin-web');

        return $user;
    }

    public function test_admin_bisa_membuat_poster(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->set('title', 'Dirgahayu RI ke-81')
            ->set('image', UploadedFile::fake()->image('poster.jpg', 1200, 500))
            ->call('save')
            ->assertHasNoErrors();

        $poster = Poster::where('title', 'Dirgahayu RI ke-81')->first();
        $this->assertNotNull($poster);
        $this->assertNotNull($poster->imageUrl());
    }

    public function test_admin_bisa_mengubah_poster_tanpa_wajib_ganti_gambar(): void
    {
        Storage::fake('public');

        $poster = Poster::factory()->create(['title' => 'Judul Lama']);
        $berkas = UploadedFile::fake()->image('poster-lama.jpg');
        $poster->addMedia($berkas->getRealPath())->usingFileName('poster-lama.jpg')->toMediaCollection('image');

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('edit', $poster->id)
            ->assertSet('title', 'Judul Lama')
            ->set('title', 'Judul Baru')
            ->set('ends_at', '2026-12-31')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editingId', null);

        $poster->refresh();
        $this->assertSame('Judul Baru', $poster->title);
        $this->assertSame('2026-12-31', $poster->ends_at->format('Y-m-d'));
        $this->assertStringContainsString('poster-lama', (string) $poster->imageUrl(), 'Gambar lama harus dipertahankan.');
        $this->assertSame(1, Poster::count());
    }

    public function test_mengubah_poster_bisa_mengganti_gambar(): void
    {
        Storage::fake('public');

        $poster = Poster::factory()->create(['title' => 'Poster']);
        $lama = UploadedFile::fake()->image('poster-lama.jpg');
        $poster->addMedia($lama->getRealPath())->usingFileName('poster-lama.jpg')->toMediaCollection('image');

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('edit', $poster->id)
            ->set('image', UploadedFile::fake()->image('poster-baru.jpg', 1200, 400))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertStringNotContainsString('poster-lama.jpg', (string) $poster->fresh()->imageUrl());
    }

    public function test_user_tanpa_permission_ditolak(): void
    {
        $anggota = User::factory()->create();
        $anggota->assignRole('anggota');

        $this->actingAs($anggota)
            ->get(route('admin.posters.index'))
            ->assertForbidden();
    }

    public function test_beranda_menampilkan_poster_yang_tayang(): void
    {
        Storage::fake('public');

        $poster = Poster::factory()->create(['title' => 'Selamat Hari Jadi Bengkalis']);
        $file = UploadedFile::fake()->image('poster.jpg');
        $poster->addMedia($file->getRealPath())
            ->usingFileName('poster.jpg')
            ->toMediaCollection('image');

        $this->get('/')
            ->assertOk()
            ->assertSee('Selamat Hari Jadi Bengkalis');
    }

    public function test_beranda_menyembunyikan_poster_nonaktif_atau_kedaluwarsa(): void
    {
        Storage::fake('public');

        $nonaktif = Poster::factory()->create(['title' => 'Poster Nonaktif', 'is_active' => false]);
        $kedaluwarsa = Poster::factory()->create(['title' => 'Poster Kedaluwarsa', 'ends_at' => now()->subDay()]);
        $belumMulai = Poster::factory()->create(['title' => 'Poster Belum Mulai', 'starts_at' => now()->addWeek()]);

        foreach ([$nonaktif, $kedaluwarsa, $belumMulai] as $poster) {
            $file = UploadedFile::fake()->image('poster.jpg');
            $poster->addMedia($file->getRealPath())
                ->usingFileName('poster.jpg')
                ->toMediaCollection('image');
        }

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Poster Nonaktif')
            ->assertDontSee('Poster Kedaluwarsa')
            ->assertDontSee('Poster Belum Mulai');
    }

    public function test_toggle_dan_hapus_poster(): void
    {
        Storage::fake('public');

        $poster = Poster::factory()->create();

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('toggleActive', $poster->id);

        $this->assertFalse($poster->fresh()->is_active);

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('deletePoster', $poster->id);

        $this->assertNull(Poster::find($poster->id));
    }

    public function test_tanggal_berakhir_sebelum_mulai_ditolak(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->set('title', 'Poster Salah Tanggal')
            ->set('image', UploadedFile::fake()->image('poster.jpg'))
            ->set('starts_at', '2026-08-17')
            ->set('ends_at', '2026-08-01')
            ->call('save')
            ->assertHasErrors('ends_at');
    }
}
