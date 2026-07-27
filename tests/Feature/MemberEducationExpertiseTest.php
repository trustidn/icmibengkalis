<?php

namespace Tests\Feature;

use App\Livewire\Admin\Members\Form as MembersForm;
use App\Livewire\Admin\Members\Index as MembersIndex;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberEducationExpertiseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_bisa_menambah_riwayat_pendidikan(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['members.view', 'members.update']);
        $member = Member::factory()->create();

        Livewire::actingAs($admin)
            ->test(MembersForm::class, ['member' => $member])
            ->set('eduLevel', 'S1')
            ->set('eduInstitution', 'Universitas Riau')
            ->set('eduMajor', 'Ekonomi')
            ->set('eduGraduatedYear', '2015')
            ->call('addEducation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('member_educations', [
            'member_id' => $member->id,
            'institution' => 'Universitas Riau',
        ]);
    }

    public function test_admin_bisa_mengisi_profesi_dan_keahlian_manual(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['members.view', 'members.update']);
        $member = Member::factory()->create();

        Livewire::actingAs($admin)
            ->test(MembersForm::class, ['member' => $member])
            ->set('profession', 'Dosen')
            ->set('expertise', 'Ekonomi Syariah, Pendidikan Karakter')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'profession' => 'Dosen',
            'expertise' => 'Ekonomi Syariah, Pendidikan Karakter',
        ]);
    }

    public function test_filter_anggota_berdasarkan_pendidikan(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('members.view');

        $matching = Member::factory()->create(['full_name' => 'Ahli Cocok']);
        $matching->educations()->create(['level' => 'S2', 'institution' => 'ITB']);

        Member::factory()->create(['full_name' => 'Anggota Lain']);

        Livewire::actingAs($admin)
            ->test(MembersIndex::class)
            ->set('education_level', 'S2')
            ->assertSee('Ahli Cocok')
            ->assertDontSee('Anggota Lain');
    }
}
