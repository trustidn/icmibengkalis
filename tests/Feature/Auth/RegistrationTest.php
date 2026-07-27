<?php

namespace Tests\Feature\Auth;

use App\Enums\MemberStatus;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = Volt::test('auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_registrasi_langsung_membuat_profil_anggota_tertaut(): void
    {
        Volt::test('auth.register')
            ->set('name', 'Anggota Baru')
            ->set('email', 'anggota.baru@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', 'anggota.baru@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('anggota'));

        $this->assertNotNull($user->member);
        $this->assertSame('Anggota Baru', $user->member->full_name);
        $this->assertSame(MemberStatus::Aktif, $user->member->status);
        $this->assertNotEmpty($user->member->nia);
        $this->assertNotEmpty($user->member->slug);
    }
}
