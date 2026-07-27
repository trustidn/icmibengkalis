<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_anggota_bisa_mengubah_profil_sendiri(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('member.profile.edit'))
            ->assertOk()
            ->assertSee($member->nia);
    }

    public function test_anggota_tidak_bisa_akses_profil_tanpa_member_terkait(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('member.profile.edit'))
            ->assertNotFound();
    }

    public function test_user_nonaktif_ditolak_akses_dan_logout_otomatis(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
