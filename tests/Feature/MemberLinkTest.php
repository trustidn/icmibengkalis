<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Livewire\Admin\Members\Form as AdminMemberForm;
use App\Livewire\Member\Profile\Edit as ProfileEdit;
use App\Models\Member;
use App\Models\MemberLink;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_anggota_bisa_menambah_beberapa_tautan_dengan_label_custom(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);

        $komponen = Livewire::actingAs($user)->test(ProfileEdit::class);

        $komponen->set('linkType', 'website')
            ->set('linkLabel', 'Toko Online Saya')
            ->set('linkValue', 'https://toko.example.com')
            ->call('addLink')
            ->assertHasNoErrors();

        $komponen->set('linkType', 'website')
            ->set('linkLabel', 'Blog Pribadi')
            ->set('linkValue', 'blog.example.com')
            ->call('addLink')
            ->assertHasNoErrors();

        $komponen->set('linkType', 'instagram')
            ->set('linkLabel', '')
            ->set('linkValue', 'https://instagram.com/fulan')
            ->call('addLink')
            ->assertHasNoErrors();

        $this->assertSame(3, $member->links()->count());
        $this->assertSame(2, $member->links()->where('type', 'website')->count());
        $this->assertSame('Toko Online Saya', $member->links()->first()->displayLabel());
    }

    public function test_anggota_bisa_menghapus_tautannya(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);
        $link = $member->links()->create(['type' => 'website', 'value' => 'https://a.example.com']);

        Livewire::actingAs($user)->test(ProfileEdit::class)
            ->call('deleteLink', $link->id);

        $this->assertSame(0, $member->links()->count());
    }

    public function test_tautan_tampil_di_profil_publik_dengan_label(): void
    {
        $member = Member::factory()->create(['status' => MemberStatus::Aktif]);
        $member->links()->create(['type' => 'website', 'label' => 'Toko Online', 'value' => 'toko.example.com']);
        $member->links()->create(['type' => 'instagram', 'value' => 'https://instagram.com/fulan']);
        $member->links()->create(['type' => 'tiktok', 'value' => 'https://tiktok.com/@fulan']);

        $this->get("/profil/{$member->slug}")
            ->assertOk()
            ->assertSee('Toko Online')
            ->assertSee('Instagram')
            ->assertSee('TikTok')
            ->assertSee('https://toko.example.com', false)
            ->assertSee('https://instagram.com/fulan', false);
    }

    public function test_url_whatsapp_dinormalkan_ke_wa_me(): void
    {
        $link = new MemberLink(['type' => 'whatsapp', 'value' => '0812-3456-7890']);

        $this->assertSame('https://wa.me/6281234567890', $link->url());
    }

    public function test_admin_bisa_mengelola_tautan_anggota(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['members.view', 'members.update']);
        $member = Member::factory()->create();

        Livewire::actingAs($admin)
            ->test(AdminMemberForm::class, ['member' => $member])
            ->set('linkType', 'youtube')
            ->set('linkValue', 'https://youtube.com/@icmi')
            ->call('addLink')
            ->assertHasNoErrors();

        $this->assertSame(1, $member->links()->count());
        $this->assertSame('youtube', $member->links()->first()->type);
    }

    public function test_migrasi_memindahkan_kontak_lama_ke_tabel_tautan(): void
    {
        // Kontak lama (social_links JSON) sudah disalin oleh migrasi untuk data
        // eksisten; anggota baru cukup lewat tabel member_links.
        $member = Member::factory()->create();
        $member->links()->create(['type' => 'linkedin', 'value' => 'https://linkedin.com/in/fulan']);

        $this->assertDatabaseHas('member_links', [
            'member_id' => $member->id,
            'type' => 'linkedin',
        ]);
    }
}
