<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Livewire\Admin\Members\Form;
use App\Livewire\Admin\Members\Index as MembersIndex;
use App\Models\District;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MembersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_bisa_mengganti_foto_dan_bio_anggota(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->givePermissionTo(['members.view', 'members.update']);
        $member = Member::factory()->create();

        Livewire::actingAs($admin)
            ->test(Form::class, ['member' => $member])
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('bio', 'Bio ditulis admin.')
            ->set('whatsapp', '0812000111')
            ->call('save')
            ->assertHasNoErrors();

        $member->refresh();
        $this->assertNotNull($member->photoUrl());
        $this->assertSame('Bio ditulis admin.', $member->bio);
        $this->assertSame('0812000111', $member->social_links['whatsapp']);

        // Admin juga bisa menghapus foto anggota
        Livewire::actingAs($admin)
            ->test(Form::class, ['member' => $member->fresh()])
            ->call('removePhoto');

        $this->assertNull($member->fresh()->photoUrl());
    }

    public function test_admin_dengan_permission_bisa_melihat_daftar_anggota(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('members.view');
        Member::factory()->create(['full_name' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_nia_digenerate_otomatis_dan_unik(): void
    {
        $one = Member::factory()->create();
        $two = Member::factory()->create();

        $this->assertNotEquals($one->nia, $two->nia);
        $this->assertStringStartsWith('ICMI-'.now()->year.'-', $one->nia);
    }

    public function test_filter_kecamatan_dan_profesi_dan_status(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('members.view');

        $bengkalis = District::factory()->create(['name' => 'Bengkalis']);

        Member::factory()->create(['full_name' => 'Anggota Cocok', 'district_id' => $bengkalis->id, 'profession' => 'Dosen', 'status' => MemberStatus::Aktif]);
        Member::factory()->create(['full_name' => 'Anggota Lain', 'status' => MemberStatus::TidakAktif]);

        Livewire::actingAs($admin)
            ->test(MembersIndex::class)
            ->set('district_id', (string) $bengkalis->id)
            ->set('profession', 'Dosen')
            ->set('status', 'aktif')
            ->assertSee('Anggota Cocok')
            ->assertDontSee('Anggota Lain');
    }

    public function test_user_tanpa_permission_ditolak_akses_anggota(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.members.index'))
            ->assertForbidden();
    }
}
