<?php

namespace Tests\Feature;

use App\Livewire\Admin\Archive\Form as ArchiveForm;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_admin_dengan_permission_bisa_upload_dokumen(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['archive.view', 'archive.create']);

        Livewire::actingAs($admin)
            ->test(ArchiveForm::class)
            ->set('title', 'SK Kepengurusan')
            ->set('doc_type', 'sk')
            ->set('access_level', 'anggota')
            ->set('file', UploadedFile::fake()->create('sk.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $document = Document::where('title', 'SK Kepengurusan')->first();
        $this->assertNotNull($document);
        $this->assertSame(1, $document->current_version);
        $this->assertSame(1, $document->versions()->count());
    }

    public function test_unggah_versi_baru_menambah_current_version(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['archive.view', 'archive.create', 'archive.update']);
        $document = Document::factory()->create(['current_version' => 1]);
        $document->versions()->create(['version_number' => 1, 'uploaded_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(ArchiveForm::class, ['document' => $document])
            ->set('file', UploadedFile::fake()->create('revisi.pdf', 50, 'application/pdf'))
            ->set('changeNote', 'Perbaikan typo')
            ->call('uploadVersion')
            ->assertHasNoErrors();

        $document->refresh();
        $this->assertSame(2, $document->current_version);
        $this->assertSame(2, $document->versions()->count());
    }

    public function test_kategori_arsip_hierarkis(): void
    {
        $parent = DocumentCategory::factory()->create(['name' => 'Administrasi']);
        $child = DocumentCategory::factory()->create(['name' => 'Surat Keluar', 'parent_id' => $parent->id]);

        $this->assertTrue($parent->children->contains($child));
    }

    public function test_dokumen_terbatas_ditolak_tanpa_permission_atau_grant(): void
    {
        $document = Document::factory()->create(['access_level' => 'terbatas']);
        $user = User::factory()->create();

        $this->assertFalse($user->can('view', $document));
    }

    public function test_dokumen_terbatas_diloloskan_lewat_grant_spesifik(): void
    {
        $document = Document::factory()->create(['access_level' => 'terbatas']);
        $user = User::factory()->create();

        $document->permissions()->create([
            'grantee_type' => 'user',
            'grantee_id' => $user->id,
            'ability' => 'view',
        ]);

        $this->assertTrue($user->fresh()->can('view', $document->fresh()));
    }

    public function test_dokumen_anggota_hanya_untuk_user_dengan_member(): void
    {
        $document = Document::factory()->create(['access_level' => 'anggota']);

        $withoutMember = User::factory()->create();
        $withMemberUser = User::factory()->create();
        Member::factory()->create(['user_id' => $withMemberUser->id]);

        $this->assertFalse($withoutMember->can('view', $document));
        $this->assertTrue($withMemberUser->can('view', $document));
    }

    public function test_user_tanpa_permission_ditolak_akses_admin_arsip(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.archive.index'))
            ->assertForbidden();
    }
}
