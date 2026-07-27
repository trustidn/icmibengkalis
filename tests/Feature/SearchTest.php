<?php

namespace Tests\Feature;

use App\Jobs\ExtractDocumentText;
use App\Livewire\Admin\Archive\Form as ArchiveForm;
use App\Models\Document;
use App\Models\Post;
use App\Models\User;
use App\Services\Archive\ArchiveService;
use App\Services\Publishing\PublishingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_pencarian_berita_menemukan_post_berdasarkan_isi(): void
    {
        Post::factory()->published()->create(['title' => 'Judul Umum', 'body' => 'Mengandung kata kunci unikkeyword di dalamnya.']);
        Post::factory()->published()->create(['title' => 'Judul Lain', 'body' => 'Tidak relevan.']);

        $found = app(PublishingService::class)->paginatePublished(search: 'unikkeyword');

        $this->assertSame(1, $found->total());
    }

    public function test_pencarian_arsip_menemukan_dokumen_dari_extracted_text(): void
    {
        $document = Document::factory()->create(['title' => 'Dokumen Umum', 'access_level' => 'publik']);
        $document->update(['extracted_text' => 'Berisi frasa khususpencarian di dalam PDF.']);

        $results = app(ArchiveService::class)->paginate(['search' => 'khususpencarian']);

        $this->assertSame(1, $results->total());
    }

    public function test_upload_dokumen_pdf_mendispatch_job_ekstraksi_teks(): void
    {
        Storage::fake('local');
        Bus::fake();

        $admin = User::factory()->create();
        $admin->givePermissionTo(['archive.view', 'archive.create']);

        Livewire::actingAs($admin)
            ->test(ArchiveForm::class)
            ->set('title', 'Dokumen PDF')
            ->set('doc_type', 'sk')
            ->set('access_level', 'anggota')
            ->set('file', UploadedFile::fake()->create('dokumen.pdf', 50, 'application/pdf'))
            ->call('save');

        Bus::assertDispatched(ExtractDocumentText::class);
    }
}
