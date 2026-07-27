<?php

namespace Tests\Feature;

use App\Livewire\Admin\Agenda\Form as AgendaForm;
use App\Livewire\Admin\Agenda\Index;
use App\Livewire\Admin\Announcements\Index as AnnouncementsIndex;
use App\Livewire\Admin\Gallery\Albums as GalleryAlbums;
use App\Models\Album;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Admin-divisi/ketua-divisi hanya boleh mengelola Pengumuman/Agenda/Galeri
 * miliknya sendiri; peran dengan permission "manage-any" (super-admin,
 * sekretaris) tetap bisa mengelola semuanya.
 */
class ContentOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_divisi_bisa_mengubah_pengumuman_miliknya_sendiri(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-divisi');
        $announcement = Announcement::factory()->create(['created_by' => $admin->id, 'title' => 'Punya Saya']);

        Livewire::actingAs($admin)
            ->test(AnnouncementsIndex::class)
            ->call('edit', $announcement->id)
            ->set('title', 'Judul Diubah')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Judul Diubah', $announcement->fresh()->title);
    }

    public function test_admin_divisi_ditolak_mengubah_pengumuman_divisi_lain(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-divisi');
        $other = User::factory()->create();
        $announcement = Announcement::factory()->create(['created_by' => $other->id]);

        Livewire::actingAs($admin)
            ->test(AnnouncementsIndex::class)
            ->call('edit', $announcement->id)
            ->assertForbidden();
    }

    public function test_sekretaris_dengan_manage_any_bisa_mengubah_pengumuman_siapa_saja(): void
    {
        $sekretaris = User::factory()->create();
        $sekretaris->assignRole('sekretaris');
        $other = User::factory()->create();
        $announcement = Announcement::factory()->create(['created_by' => $other->id]);

        Livewire::actingAs($sekretaris)
            ->test(AnnouncementsIndex::class)
            ->call('edit', $announcement->id)
            ->set('title', 'Diubah Sekretaris')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Diubah Sekretaris', $announcement->fresh()->title);
    }

    public function test_admin_divisi_ditolak_menghapus_agenda_divisi_lain(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-divisi');
        $other = User::factory()->create();
        $event = Event::factory()->create(['created_by' => $other->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('delete', $event->id)
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_admin_divisi_bisa_mengubah_agenda_miliknya_sendiri(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-divisi');
        $event = Event::factory()->create(['created_by' => $admin->id]);

        Livewire::actingAs($admin)
            ->test(AgendaForm::class, ['event' => $event])
            ->set('title', 'Agenda Diubah')
            ->call('save');

        $this->assertSame('Agenda Diubah', $event->fresh()->title);
    }

    public function test_admin_divisi_ditolak_menghapus_album_divisi_lain(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-divisi');
        $other = User::factory()->create();
        $album = Album::factory()->create(['created_by' => $other->id]);

        Livewire::actingAs($admin)
            ->test(GalleryAlbums::class)
            ->call('delete', $album->id)
            ->assertForbidden();

        $this->assertDatabaseHas('albums', ['id' => $album->id]);
    }

    public function test_album_baru_tercatat_kepemilikannya(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-divisi');

        Livewire::actingAs($admin)
            ->test(GalleryAlbums::class)
            ->call('create')
            ->set('title', 'Album Divisi Saya')
            ->set('type', 'foto')
            ->call('save');

        $this->assertDatabaseHas('albums', ['title' => 'Album Divisi Saya', 'created_by' => $admin->id]);
    }
}
