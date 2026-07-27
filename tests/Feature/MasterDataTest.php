<?php

namespace Tests\Feature;

use App\Livewire\Admin\Expertise\Fields as ExpertiseFields;
use App\Livewire\Admin\Professions\Index as ProfessionsIndex;
use App\Models\ExpertiseField;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_dengan_permission_bisa_kelola_profesi(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['members.view', 'members.update', 'members.delete']);

        Livewire::actingAs($admin)
            ->test(ProfessionsIndex::class)
            ->set('name', 'Dosen')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('professions', ['name' => 'Dosen']);
    }

    public function test_user_tanpa_permission_ditolak_akses_profesi(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.professions.index'))
            ->assertForbidden();
    }

    public function test_admin_dengan_permission_bisa_kelola_bidang_keahlian(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['expertise.view', 'expertise.manage-fields']);

        Livewire::actingAs($admin)
            ->test(ExpertiseFields::class)
            ->set('name', 'Sains Data')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expertise_fields', ['name' => 'Sains Data']);
    }

    public function test_expertise_field_mendukung_hierarki_parent_child(): void
    {
        $parent = ExpertiseField::factory()->create(['name' => 'Ekonomi Digital']);
        $child = ExpertiseField::factory()->create(['name' => 'Fintech', 'parent_id' => $parent->id]);

        $this->assertTrue($parent->children->contains($child));
        $this->assertSame($parent->id, $child->parent->id);
    }

    public function test_user_tanpa_permission_ditolak_akses_bidang_keahlian(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.expertise.fields'))
            ->assertForbidden();
    }
}
