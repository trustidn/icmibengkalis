<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_guest_hanya_melihat_dokumen_publik_di_daftar(): void
    {
        Storage::fake('local');

        $this->documentWithFile('publik', 'Dokumen Publik');
        $this->documentWithFile('anggota', 'Dokumen Anggota Saja');

        $this->get(route('archive.index'))
            ->assertOk()
            ->assertSee('Dokumen Publik')
            ->assertDontSee('Dokumen Anggota Saja');
    }

    public function test_anggota_login_melihat_dokumen_anggota_juga(): void
    {
        Storage::fake('local');

        $this->documentWithFile('anggota', 'Dokumen Anggota Saja');

        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('archive.index'))
            ->assertSee('Dokumen Anggota Saja');
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
}
