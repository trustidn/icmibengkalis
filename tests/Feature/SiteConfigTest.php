<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\SiteConfig;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        SiteSetting::forgetCurrent();
    }

    public function test_super_admin_bisa_membuka_konfigurasi_web(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get('/admin/konfigurasi')
            ->assertOk()
            ->assertSee('Konfigurasi Web');
    }

    public function test_peran_selain_super_admin_ditolak(): void
    {
        $sekretaris = User::factory()->create();
        $sekretaris->assignRole('sekretaris');

        $this->actingAs($sekretaris)
            ->get('/admin/konfigurasi')
            ->assertForbidden();
    }

    public function test_admin_web_bisa_membuka_konfigurasi_dan_halaman_statis(): void
    {
        $adminWeb = User::factory()->create();
        $adminWeb->assignRole('admin-web');

        $this->actingAs($adminWeb)
            ->get('/admin/konfigurasi')
            ->assertOk();

        $this->actingAs($adminWeb)
            ->get(route('admin.pages.index'))
            ->assertOk();
    }

    public function test_guest_dialihkan_ke_login(): void
    {
        $this->get('/admin/konfigurasi')->assertRedirect('/login');
    }

    public function test_super_admin_bisa_menyimpan_konfigurasi(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Livewire::actingAs($admin)
            ->test(SiteConfig::class)
            ->set('site_name', 'ICMI Bengkalis Baru')
            ->set('email', 'sekretariat@icmibengkalis.or.id')
            ->set('facebook', 'https://facebook.com/icmibengkalis')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('saved', true);

        SiteSetting::forgetCurrent();

        $this->assertDatabaseHas('site_settings', [
            'site_name' => 'ICMI Bengkalis Baru',
            'email' => 'sekretariat@icmibengkalis.or.id',
            'facebook' => 'https://facebook.com/icmibengkalis',
        ]);
    }

    public function test_nama_situs_wajib_diisi(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Livewire::actingAs($admin)
            ->test(SiteConfig::class)
            ->set('site_name', '')
            ->call('save')
            ->assertHasErrors(['site_name' => 'required']);
    }

    public function test_navbar_publik_tidak_menampilkan_tautan_pendaftaran(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee(route('register'));
    }

    public function test_super_admin_bisa_mengunggah_favicon(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Livewire::actingAs($admin)
            ->test(SiteConfig::class)
            ->set('favicon', UploadedFile::fake()->image('ikon.png', 64, 64))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('saved', true);

        SiteSetting::forgetCurrent();

        $this->assertNotNull(SiteSetting::current()->faviconUrl());
    }

    public function test_head_menyertakan_tag_favicon_dengan_fallback_bawaan(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="icon" href="/favicon.ico" />', false);
    }

    public function test_favicon_bisa_dihapus(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        Livewire::actingAs($admin)
            ->test(SiteConfig::class)
            ->set('favicon', UploadedFile::fake()->image('ikon.png', 64, 64))
            ->call('save');

        Livewire::actingAs($admin)
            ->test(SiteConfig::class)
            ->call('removeFavicon');

        SiteSetting::forgetCurrent();

        $this->assertNull(SiteSetting::current()->faviconUrl());
    }
}
