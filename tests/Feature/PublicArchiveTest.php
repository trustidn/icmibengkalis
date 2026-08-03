<?php

namespace Tests\Feature;

use App\Livewire\Public\Archive\Show;
use App\Livewire\Public\Archive\Upload;
use App\Models\Document;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PublicArchiveTest extends TestCase
{
    use RefreshDatabase;

    private function documentWithFile(string $accessLevel, string $title): Document
    {
        $document = Document::factory()->create(['access_level' => $accessLevel, 'title' => $title]);
        $version = $document->versions()->create(['version_number' => 1, 'uploaded_by' => $document->uploaded_by]);
        $version->addMedia(UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'))->toMediaCollection('versions', 'local');

        return $document;
    }

    public function test_guest_melihat_dokumen_anggota_sebagai_kartu_terkunci(): void
    {
        Storage::fake('local');

        $this->documentWithFile('publik', 'Dokumen Publik');
        $anggotaDoc = $this->documentWithFile('anggota', 'Dokumen Anggota Saja');

        $this->get(route('archive.index'))
            ->assertOk()
            ->assertSee('Dokumen Publik')
            // Dokumen anggota TAMPIL di daftar tapi terkunci: ada judul + ajakan masuk,
            // TANPA tautan ke halaman detailnya.
            ->assertSee('Dokumen Anggota Saja')
            ->assertSee('Khusus Anggota')
            ->assertSee('Masuk untuk mengakses')
            ->assertDontSee(route('archive.show', $anggotaDoc->slug));
    }

    public function test_dokumen_pengurus_tetap_tersembunyi_dari_daftar_publik(): void
    {
        Storage::fake('local');

        $this->documentWithFile('pengurus', 'Notulen Rapat Pengurus');

        $this->get(route('archive.index'))
            ->assertOk()
            ->assertDontSee('Notulen Rapat Pengurus');
    }

    public function test_anggota_login_melihat_dokumen_anggota_dengan_tautan_aktif(): void
    {
        Storage::fake('local');

        $document = $this->documentWithFile('anggota', 'Dokumen Anggota Saja');

        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('archive.index'))
            ->assertSee('Dokumen Anggota Saja')
            ->assertSee(route('archive.show', $document->slug))
            ->assertDontSee('Masuk untuk mengakses');
    }

    public function test_user_login_tanpa_member_melihat_kartu_terkunci(): void
    {
        Storage::fake('local');

        $this->documentWithFile('anggota', 'Dokumen Anggota Saja');

        $this->actingAs(User::factory()->create())
            ->get(route('archive.index'))
            ->assertSee('Dokumen Anggota Saja')
            ->assertSee('Hanya untuk anggota ICMI Bengkalis.');
    }

    public function test_detail_dokumen_anggota_ditolak_untuk_guest(): void
    {
        Storage::fake('local');
        $document = $this->documentWithFile('anggota', 'Dokumen Anggota Detail');

        $this->get(route('archive.show', $document->slug))->assertForbidden();
    }

    public function test_halaman_detail_dokumen_publik_bisa_diakses_guest(): void
    {
        Storage::fake('local');
        $document = $this->documentWithFile('publik', 'Dokumen Publik Detail');

        $this->get(route('archive.show', $document->slug))
            ->assertOk()
            ->assertSee('Dokumen Publik Detail');
    }

    public function test_halaman_detail_dokumen_terbatas_ditolak_untuk_guest(): void
    {
        Storage::fake('local');
        $document = $this->documentWithFile('terbatas', 'Dokumen Rahasia');

        $this->get(route('archive.show', $document->slug))->assertForbidden();
    }

    public function test_user_login_bisa_mengunggah_dokumen(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Upload::class)
            ->set('title', 'Materi Kajian Anggota')
            ->set('doc_type', Document::factory()->make()->doc_type->value)
            ->set('access_level', 'anggota')
            ->set('file', UploadedFile::fake()->create('materi.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $document = Document::where('title', 'Materi Kajian Anggota')->first();
        $this->assertNotNull($document);
        $this->assertSame($user->id, $document->uploaded_by);
        $this->assertNotNull($document->latestVersion());
    }

    public function test_guest_dialihkan_dari_halaman_unggah(): void
    {
        $this->get(route('archive.upload'))->assertRedirect(route('login'));
    }

    public function test_pengunggah_bisa_menghapus_dokumen_sendiri_user_lain_tidak(): void
    {
        Storage::fake('local');
        $pemilik = User::factory()->create();
        $orangLain = User::factory()->create();
        $document = $this->documentWithFile('publik', 'Dokumen Milik Sendiri');
        $document->update(['uploaded_by' => $pemilik->id]);

        // User lain: tombol tidak tampil & aksi ditolak
        Livewire::actingAs($orangLain)
            ->test(Show::class, ['slug' => $document->slug])
            ->assertDontSee('confirm-delete-document')
            ->call('delete')
            ->assertForbidden();

        $this->assertNotNull(Document::find($document->id));

        // Pemilik: berhasil menghapus
        Livewire::actingAs($pemilik)
            ->test(Show::class, ['slug' => $document->slug])
            ->call('delete')
            ->assertHasNoErrors();

        $this->assertNull(Document::find($document->id));
    }

    public function test_nav_arsip_tampil_hanya_untuk_user_login(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Arsip Digital');

        $user = User::factory()->create();
        $this->actingAs($user)->get('/')->assertOk()->assertSee('Arsip Digital');
    }
}
