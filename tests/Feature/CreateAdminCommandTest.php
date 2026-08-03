<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_membuat_akun_super_admin_baru(): void
    {
        $this->artisan('icmi:admin', [
            '--name' => 'Admin Portal',
            '--email' => 'admin@icmibengkalis.or.id',
            '--password' => 'rahasia-kuat-123',
        ])->assertSuccessful();

        $user = User::where('email', 'admin@icmibengkalis.or.id')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('super-admin'));
        $this->assertNotNull($user->email_verified_at, 'Akun harus terverifikasi agar bisa masuk dashboard.');
        $this->assertTrue($user->can('settings.manage'));
    }

    public function test_email_yang_sudah_ada_hanya_ditambahi_peran(): void
    {
        $existing = User::factory()->create(['email' => 'lama@contoh.test']);

        $this->artisan('icmi:admin', ['--email' => 'lama@contoh.test', '--role' => 'admin-web'])
            ->assertSuccessful();

        $this->assertSame(1, User::where('email', 'lama@contoh.test')->count());
        $this->assertTrue($existing->fresh()->hasRole('admin-web'));
    }

    public function test_peran_tidak_dikenal_ditolak(): void
    {
        $this->artisan('icmi:admin', [
            '--name' => 'X',
            '--email' => 'x@contoh.test',
            '--password' => 'rahasia-kuat-123',
            '--role' => 'peran-ngawur',
        ])->assertFailed();

        $this->assertNull(User::where('email', 'x@contoh.test')->first());
    }

    public function test_password_pendek_ditolak(): void
    {
        $this->artisan('icmi:admin', [
            '--name' => 'X',
            '--email' => 'pendek@contoh.test',
            '--password' => '123',
        ])->assertFailed();

        $this->assertNull(User::where('email', 'pendek@contoh.test')->first());
    }
}
