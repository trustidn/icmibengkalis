<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_publik_melihat_agenda_mendatang_yang_tayang(): void
    {
        $event = Event::factory()->create(['title' => 'Rapat Kerja Tahunan', 'starts_at' => now()->addWeek(), 'is_published' => true]);

        $this->get(route('agenda.index'))
            ->assertOk()
            ->assertSee('Rapat Kerja Tahunan');

        $this->get(route('agenda.show', $event->slug))
            ->assertOk()
            ->assertSee('Rapat Kerja Tahunan');
    }

    public function test_agenda_belum_tayang_404_di_halaman_publik(): void
    {
        $event = Event::factory()->create(['is_published' => false]);

        $this->get(route('agenda.show', $event->slug))->assertNotFound();
    }

    public function test_user_tanpa_permission_ditolak_akses_admin_agenda(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.agenda.index'))
            ->assertForbidden();
    }
}
