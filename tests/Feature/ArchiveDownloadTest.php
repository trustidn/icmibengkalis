<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    private function documentWithFile(string $accessLevel): Document
    {
        $document = Document::factory()->create(['access_level' => $accessLevel]);
        $version = $document->versions()->create(['version_number' => 1, 'uploaded_by' => $document->uploaded_by]);
        $version->addMedia(UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'))->toMediaCollection('versions', 'local');

        return $document;
    }

    public function test_guest_bisa_unduh_dokumen_publik(): void
    {
        $document = $this->documentWithFile('publik');

        $this->get(route('archive.download', $document))->assertOk();
    }

    public function test_guest_ditolak_untuk_dokumen_anggota(): void
    {
        $document = $this->documentWithFile('anggota');

        $this->get(route('archive.download', $document))->assertForbidden();
    }

    public function test_anggota_biasa_ditolak_untuk_dokumen_pengurus(): void
    {
        $document = $this->documentWithFile('pengurus');
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('archive.download', $document))
            ->assertForbidden();
    }

    public function test_unduh_versi_lama_masih_bisa_diakses(): void
    {
        $document = $this->documentWithFile('publik');
        $v2 = $document->versions()->create(['version_number' => 2, 'uploaded_by' => $document->uploaded_by]);
        $v2->addMedia(UploadedFile::fake()->create('doc-v2.pdf', 10, 'application/pdf'))->toMediaCollection('versions', 'local');
        $document->update(['current_version' => 2]);

        $this->get(route('archive.download.version', [$document, 1]))->assertOk();
        $this->get(route('archive.download.version', [$document, 2]))->assertOk();
    }
}
