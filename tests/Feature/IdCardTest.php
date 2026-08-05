<?php

namespace Tests\Feature;

use App\Livewire\Admin\IdCard\Index as AdminIdCardIndex;
use App\Models\IdCardEvent;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class IdCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('idcard.manage');

        return $admin;
    }

    private function makeMemberUser(): User
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_admin_bisa_membuat_kegiatan_dengan_desain_latar(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();

        Livewire::actingAs($admin)
            ->test(AdminIdCardIndex::class)
            ->call('create')
            ->set('name', 'Pelantikan Pengurus 2026-2031')
            ->set('event_date', '2026-09-10')
            ->set('background', UploadedFile::fake()->image('latar.png', 1080, 1712))
            ->call('save')
            ->assertHasNoErrors();

        $event = IdCardEvent::first();
        $this->assertSame('Pelantikan Pengurus 2026-2031', $event->name);
        $this->assertTrue($event->is_active);
        $this->assertNotNull($event->getFirstMedia('background'));
    }

    public function test_kegiatan_baru_wajib_punya_desain_latar(): void
    {
        Livewire::actingAs($this->makeAdmin())
            ->test(AdminIdCardIndex::class)
            ->call('create')
            ->set('name', 'Tanpa Latar')
            ->call('save')
            ->assertHasErrors(['background']);
    }

    public function test_admin_bisa_mengubah_nama_tanpa_ganti_latar(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();
        $event = IdCardEvent::factory()->create(['name' => 'Nama Lama']);
        $latar = UploadedFile::fake()->image('latar.png', 1080, 1712);
        $event->addMedia($latar->getRealPath())->usingFileName('latar.png')->toMediaCollection('background');

        Livewire::actingAs($admin)
            ->test(AdminIdCardIndex::class)
            ->call('edit', $event->id)
            ->assertSet('name', 'Nama Lama')
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $event->refresh();
        $this->assertSame('Nama Baru', $event->name);
        $this->assertNotNull($event->getFirstMedia('background'));
    }

    public function test_user_tanpa_permission_ditolak_akses_admin_id_card(): void
    {
        $this->actingAs($this->makeMemberUser())
            ->get(route('admin.idcard.index'))
            ->assertForbidden();
    }

    public function test_anggota_otomatis_melihat_kartu_kegiatan_yang_dibuka(): void
    {
        $user = $this->makeMemberUser();
        $event = IdCardEvent::factory()->create();

        $this->actingAs($user)
            ->get(route('member.idcard.index'))
            ->assertOk()
            ->assertSee($event->name)
            ->assertSee('Unduh PDF')
            ->assertSee($user->member->full_name);
    }

    public function test_kegiatan_ditutup_tidak_tampil_untuk_anggota(): void
    {
        $user = $this->makeMemberUser();
        $event = IdCardEvent::factory()->create(['name' => 'Kegiatan Rahasia', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('member.idcard.index'))
            ->assertOk()
            ->assertDontSee('Kegiatan Rahasia');
    }

    public function test_anggota_bisa_unduh_pdf_kartu_sendiri(): void
    {
        $user = $this->makeMemberUser();
        $event = IdCardEvent::factory()->create();

        $response = $this->actingAs($user)->get(route('member.idcard.print', $event));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_pdf_kegiatan_ditutup_tidak_bisa_diunduh_anggota(): void
    {
        $user = $this->makeMemberUser();
        $event = IdCardEvent::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get(route('member.idcard.print', $event))
            ->assertNotFound();
    }

    public function test_user_tanpa_member_tidak_punya_id_card(): void
    {
        $user = User::factory()->create();
        $event = IdCardEvent::factory()->create();

        $this->actingAs($user)->get(route('member.idcard.index'))->assertForbidden();
        $this->actingAs($user)->get(route('member.idcard.print', $event))->assertForbidden();
    }

    public function test_admin_bisa_cetak_massal_pdf_semua_anggota_aktif(): void
    {
        $admin = $this->makeAdmin();
        $event = IdCardEvent::factory()->create();
        foreach ([1, 2, 3] as $i) {
            Member::factory()->create(['nia' => "ICMI-TEST-000{$i}"]);
        }

        $response = $this->actingAs($admin)->get(route('admin.idcard.print-all', $event));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_cetak_massal_tanpa_permission_ditolak(): void
    {
        $user = $this->makeMemberUser();
        $event = IdCardEvent::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.idcard.print-all', $event))
            ->assertForbidden();
    }
}
