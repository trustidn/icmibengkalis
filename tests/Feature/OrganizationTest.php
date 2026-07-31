<?php

namespace Tests\Feature;

use App\Livewire\Admin\Organization\AssignmentForm;
use App\Livewire\Admin\Organization\Periods;
use App\Livewire\Admin\Organization\UnitTree;
use App\Models\Member;
use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use App\Models\User;
use App\Services\Organization\OrgChartService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_bisa_membuat_periode_unit_dan_penugasan(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['organization.view', 'organization.manage']);

        Livewire::actingAs($admin)
            ->test(Periods::class)
            ->set('name', '2025-2030')
            ->set('starts_at', '2025-01-01')
            ->set('ends_at', '2030-01-01')
            ->call('create')
            ->assertHasNoErrors();

        $period = OrgPeriod::first();
        $this->assertNotNull($period);

        Livewire::actingAs($admin)
            ->test(UnitTree::class, ['period' => $period])
            ->set('name', 'Bidang Ekonomi')
            ->call('addUnit')
            ->assertHasNoErrors();

        $unit = OrgUnit::first();
        $member = Member::factory()->create();

        Livewire::actingAs($admin)
            ->test(AssignmentForm::class, ['unit' => $unit])
            ->set('member_id', $member->id)
            ->set('position_title', 'Ketua Bidang')
            ->call('addAssignment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('org_assignments', [
            'org_unit_id' => $unit->id,
            'member_id' => $member->id,
            'position_title' => 'Ketua Bidang',
        ]);
    }

    public function test_salin_struktur_ke_periode_baru(): void
    {
        $from = OrgPeriod::factory()->create();
        $to = OrgPeriod::factory()->create();
        $parent = OrgUnit::factory()->create(['org_period_id' => $from->id, 'name' => 'Induk']);
        OrgUnit::factory()->create(['org_period_id' => $from->id, 'parent_id' => $parent->id, 'name' => 'Anak']);

        app(OrgChartService::class)->copyStructureToNewPeriod($from, $to);

        $this->assertDatabaseHas('org_units', ['org_period_id' => $to->id, 'name' => 'Induk']);
        $this->assertDatabaseHas('org_units', ['org_period_id' => $to->id, 'name' => 'Anak']);
        $this->assertSame(2, OrgUnit::where('org_period_id', $to->id)->count());
    }

    public function test_user_tanpa_permission_ditolak_akses_organisasi(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.organization.periods'))
            ->assertForbidden();
    }

    public function test_admin_bisa_mengubah_nama_dan_tahun_periode(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['organization.view', 'organization.manage']);
        $period = OrgPeriod::factory()->create([
            'name' => '2025-2030',
            'starts_at' => '2025-01-01',
            'ends_at' => '2030-01-01',
        ]);

        Livewire::actingAs($admin)
            ->test(Periods::class)
            ->call('startEdit', $period->id)
            ->assertSet('editingName', '2025-2030')
            ->set('editingName', '2026-2031')
            ->set('editingStartsAt', '2026-01-01')
            ->set('editingEndsAt', '2031-01-01')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $period->refresh();
        $this->assertSame('2026-2031', $period->name);
        $this->assertSame('2026-01-01', $period->starts_at->format('Y-m-d'));
        $this->assertSame('2031-01-01', $period->ends_at->format('Y-m-d'));
    }

    public function test_ubah_periode_menolak_tanggal_berakhir_sebelum_mulai(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['organization.view', 'organization.manage']);
        $period = OrgPeriod::factory()->create();

        Livewire::actingAs($admin)
            ->test(Periods::class)
            ->call('startEdit', $period->id)
            ->set('editingStartsAt', '2030-01-01')
            ->set('editingEndsAt', '2025-01-01')
            ->call('saveEdit')
            ->assertHasErrors('editingEndsAt');
    }

    public function test_halaman_kelola_unit_menampilkan_sub_unit_semua_level(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['organization.view', 'organization.manage']);
        $period = OrgPeriod::factory()->create();

        $level1 = OrgUnit::factory()->create(['org_period_id' => $period->id, 'name' => 'Bidang Ekonomi']);
        $level2 = OrgUnit::factory()->create(['org_period_id' => $period->id, 'parent_id' => $level1->id, 'name' => 'Sub Bidang UMKM']);
        $level3 = OrgUnit::factory()->create(['org_period_id' => $period->id, 'parent_id' => $level2->id, 'name' => 'Seksi Pelatihan UMKM']);
        $level4 = OrgUnit::factory()->create(['org_period_id' => $period->id, 'parent_id' => $level3->id, 'name' => 'Tim Kurikulum Pelatihan']);

        $this->actingAs($admin)
            ->get(route('admin.organization.units', $period))
            ->assertOk()
            ->assertSee('Bidang Ekonomi')
            ->assertSee('Sub Bidang UMKM')
            ->assertSee('Seksi Pelatihan UMKM')
            ->assertSee('Tim Kurikulum Pelatihan');
    }

    public function test_bagan_publik_menampilkan_sub_unit_level_dalam(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true]);
        $level1 = OrgUnit::factory()->create(['org_period_id' => $period->id, 'name' => 'Bidang Ekonomi']);
        $level2 = OrgUnit::factory()->create(['org_period_id' => $period->id, 'parent_id' => $level1->id, 'name' => 'Sub Bidang UMKM']);
        $level3 = OrgUnit::factory()->create(['org_period_id' => $period->id, 'parent_id' => $level2->id, 'name' => 'Seksi Pelatihan UMKM']);

        $this->get(route('org-chart.show'))
            ->assertOk()
            ->assertSee('Seksi Pelatihan UMKM');
    }

    public function test_admin_bisa_mengganti_nama_unit(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['organization.view', 'organization.manage']);
        $period = OrgPeriod::factory()->create();
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id, 'name' => 'Bidang Lama']);

        Livewire::actingAs($admin)
            ->test(UnitTree::class, ['period' => $period])
            ->call('startRename', $unit->id)
            ->assertSet('renamingName', 'Bidang Lama')
            ->set('renamingName', 'Bidang Ekonomi Syariah')
            ->call('saveRename')
            ->assertHasNoErrors();

        $this->assertSame('Bidang Ekonomi Syariah', $unit->fresh()->name);
    }

    public function test_penugasan_tokoh_eksternal_tanpa_member(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['organization.view', 'organization.manage']);
        $period = OrgPeriod::factory()->create();
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id, 'name' => 'Dewan Penasehat']);

        Livewire::actingAs($admin)
            ->test(AssignmentForm::class, ['unit' => $unit])
            ->set('external_name', 'Prof. Dr. H. Fulan bin Fulan')
            ->set('position_title', 'Ketua Dewan Penasehat')
            ->call('addAssignment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('org_assignments', [
            'org_unit_id' => $unit->id,
            'member_id' => null,
            'external_name' => 'Prof. Dr. H. Fulan bin Fulan',
            'position_title' => 'Ketua Dewan Penasehat',
        ]);
    }

    public function test_penugasan_tanpa_member_dan_tanpa_nama_eksternal_ditolak(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['organization.view', 'organization.manage']);
        $period = OrgPeriod::factory()->create();
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id]);

        Livewire::actingAs($admin)
            ->test(AssignmentForm::class, ['unit' => $unit])
            ->set('position_title', 'Penasehat')
            ->call('addAssignment')
            ->assertHasErrors(['member_id', 'external_name']);
    }

    public function test_bagan_publik_menampilkan_tokoh_eksternal(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true]);
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id, 'name' => 'Dewan Penasehat']);
        $unit->assignments()->create([
            'external_name' => 'Prof. Dr. H. Fulan bin Fulan',
            'position_title' => 'Ketua Dewan Penasehat',
        ]);

        $this->get(route('org-chart.show'))
            ->assertOk()
            ->assertSee('Prof. Dr. H. Fulan bin Fulan')
            ->assertSee('Ketua Dewan Penasehat');
    }
}
