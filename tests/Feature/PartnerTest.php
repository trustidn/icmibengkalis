<?php

namespace Tests\Feature;

use App\Livewire\Admin\Partners\Index;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function adminWeb(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin-web');

        return $user;
    }

    public function test_admin_bisa_membuat_partner_dengan_logo(): void
    {
        Storage::fake('public');

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->set('name', 'Pemkab Bengkalis')
            ->set('url', 'https://bengkaliskab.go.id')
            ->set('logo', UploadedFile::fake()->image('logo.png', 200, 100))
            ->call('save')
            ->assertHasNoErrors();

        $partner = Partner::where('name', 'Pemkab Bengkalis')->first();
        $this->assertNotNull($partner);
        $this->assertNotNull($partner->logoUrl());
    }

    public function test_partner_tanpa_logo_tampil_dengan_monogram(): void
    {
        Partner::factory()->create(['name' => 'Baznas Bengkalis']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Mitra Kami')
            ->assertSee('Baznas Bengkalis')
            ->assertSee('BB');
    }

    public function test_beranda_menyembunyikan_section_bila_tidak_ada_partner_aktif(): void
    {
        Partner::factory()->create(['name' => 'Partner Nonaktif', 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Mitra Kami')
            ->assertDontSee('Partner Nonaktif');
    }

    public function test_urutan_partner_mengikuti_sort_order(): void
    {
        Partner::factory()->create(['name' => 'Partner Kedua', 'sort_order' => 2]);
        Partner::factory()->create(['name' => 'Partner Pertama', 'sort_order' => 1]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Partner Pertama', 'Partner Kedua']);
    }

    public function test_user_tanpa_permission_ditolak(): void
    {
        $anggota = User::factory()->create();
        $anggota->assignRole('anggota');

        $this->actingAs($anggota)
            ->get(route('admin.partners.index'))
            ->assertForbidden();
    }

    public function test_toggle_dan_hapus_partner(): void
    {
        $partner = Partner::factory()->create();

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('toggleActive', $partner->id);

        $this->assertFalse($partner->fresh()->is_active);

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('deletePartner', $partner->id);

        $this->assertNull(Partner::find($partner->id));
    }
}
