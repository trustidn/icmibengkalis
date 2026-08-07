<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profil_anggota_aktif_bisa_diakses_via_slug(): void
    {
        $member = Member::factory()->create(['full_name' => 'Ahmad Fauzi Pratama', 'status' => MemberStatus::Aktif]);

        $this->get("/profil/{$member->slug}")
            ->assertOk()
            ->assertSee('Ahmad Fauzi Pratama');
    }

    public function test_profil_anggota_aktif_bisa_diakses_via_id(): void
    {
        $member = Member::factory()->create(['full_name' => 'Budi Santoso', 'status' => MemberStatus::Aktif]);

        $this->get("/profil/{$member->id}")
            ->assertOk()
            ->assertSee('Budi Santoso');
    }

    public function test_tombol_edit_profil_tampil_hanya_untuk_pemilik_profil(): void
    {
        $owner = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $owner->id, 'status' => MemberStatus::Aktif]);
        $orangLain = User::factory()->create();

        // Pemilik profil melihat tombol Edit Profil
        $this->actingAs($owner)
            ->get("/profil/{$member->slug}")
            ->assertOk()
            ->assertSee('Edit Profil');

        // User lain tidak
        $this->actingAs($orangLain)
            ->get("/profil/{$member->slug}")
            ->assertOk()
            ->assertDontSee('Edit Profil');

        // Guest tidak
        $this->post('/logout');
        $this->get("/profil/{$member->slug}")
            ->assertOk()
            ->assertDontSee('Edit Profil');
    }

    public function test_profil_anggota_tidak_aktif_404(): void
    {
        $member = Member::factory()->create(['status' => MemberStatus::TidakAktif]);

        $this->get("/profil/{$member->slug}")->assertNotFound();
    }

    public function test_identifier_tidak_ditemukan_404(): void
    {
        $this->get('/profil/tidak-ada-orangnya')->assertNotFound();
    }

    public function test_profil_menampilkan_bio_dan_bidang_keahlian(): void
    {
        $member = Member::factory()->create([
            'status' => MemberStatus::Aktif,
            'bio' => 'Saya seorang pendidik dan peneliti.',
            'expertise' => 'Ekonomi Syariah, Pendidikan Karakter',
        ]);

        $this->get("/profil/{$member->slug}")
            ->assertSee('Saya seorang pendidik dan peneliti.')
            ->assertSee('Ekonomi Syariah');
    }

    public function test_kontak_tidak_tampil_jika_show_contact_public_false(): void
    {
        $member = Member::factory()->create([
            'status' => MemberStatus::Aktif,
            'show_contact_public' => false,
        ]);
        $member->links()->create(['type' => 'whatsapp', 'value' => '081234567890']);

        $this->get("/profil/{$member->slug}")->assertDontSee('wa.me', false);
    }

    public function test_kontak_tampil_jika_show_contact_public_true(): void
    {
        $member = Member::factory()->create([
            'status' => MemberStatus::Aktif,
            'show_contact_public' => true,
        ]);
        $member->links()->create(['type' => 'whatsapp', 'value' => '081234567890']);

        $this->get("/profil/{$member->slug}")->assertSee('wa.me/6281234567890', false);
    }

    public function test_profil_menampilkan_tombol_bagikan_dan_url_lengkap(): void
    {
        $member = Member::factory()->create(['status' => MemberStatus::Aktif]);

        $this->get("/profil/{$member->slug}")
            ->assertSee('Bagikan Profil')
            ->assertSee(route('profiles.show', $member->slug));
    }
}
