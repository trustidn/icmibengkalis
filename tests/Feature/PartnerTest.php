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

    public function test_admin_bisa_mengubah_partner_dan_mengganti_logo(): void
    {
        Storage::fake('public');

        $partner = Partner::factory()->create(['name' => 'Nama Lama', 'url' => 'https://lama.test', 'sort_order' => 5]);

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('edit', $partner->id)
            ->assertSet('name', 'Nama Lama')
            ->assertSet('url', 'https://lama.test')
            ->assertSet('sort_order', 5)
            ->set('name', 'Nama Baru')
            ->set('url', 'https://baru.test')
            ->set('logo', UploadedFile::fake()->image('logo-baru.png', 200, 100))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('editingId', null);

        $partner->refresh();
        $this->assertSame('Nama Baru', $partner->name);
        $this->assertSame('https://baru.test', $partner->url);
        $this->assertNotNull($partner->logoUrl(), 'Logo baru harus tersimpan.');
        $this->assertSame(1, Partner::count(), 'Mengubah tidak boleh membuat data baru.');
    }

    public function test_mengubah_tanpa_logo_baru_mempertahankan_logo_lama(): void
    {
        Storage::fake('public');

        $partner = Partner::factory()->create(['name' => 'Mitra']);
        $berkas = UploadedFile::fake()->image('logo-lama.png');
        $partner->addMedia($berkas->getRealPath())->usingFileName('logo-lama.png')->toMediaCollection('logo');

        Livewire::actingAs($this->adminWeb())
            ->test(Index::class)
            ->call('edit', $partner->id)
            ->set('name', 'Mitra Diperbarui')
            ->call('save')
            ->assertHasNoErrors();

        $partner->refresh();
        $this->assertSame('Mitra Diperbarui', $partner->name);
        $this->assertStringContainsString('logo-lama', (string) $partner->logoUrl());
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
