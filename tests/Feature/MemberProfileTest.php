<?php

namespace Tests\Feature;

use App\Livewire\Member\Profile\Edit as ProfileEdit;
use App\Models\Member;
use App\Models\User;
use App\Services\Membership\MemberService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MemberProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_anggota_bisa_mengisi_profil_lengkap(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(ProfileEdit::class)
            ->set('full_name', 'Nama Diperbarui')
            ->set('gender', 'L')
            ->set('birth_place', 'Bengkalis')
            ->set('birth_date', '1990-01-01')
            ->set('institution', 'Universitas Riau')
            ->set('profession', 'Dosen')
            ->set('expertise', 'Ekonomi Syariah')
            ->set('bio', 'Ringkasan singkat tentang saya.')
            ->set('whatsapp', '08123456789')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'Nama Diperbarui',
            'profession' => 'Dosen',
            'bio' => 'Ringkasan singkat tentang saya.',
        ]);
    }

    public function test_simpan_profil_dengan_field_opsional_kosong_menulis_null_bukan_string_kosong(): void
    {
        // Regresi produksi: MariaDB strict menolak '' untuk kolom DATE (birth_date)
        // sehingga simpan profil (termasuk saat hanya ganti foto) error 500.
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id, 'birth_date' => null, 'gender' => null, 'bio' => null]);

        Livewire::actingAs($user)
            ->test(ProfileEdit::class)
            ->set('full_name', 'Profil Minim')
            ->call('save')
            ->assertHasNoErrors();

        $member->refresh();
        $this->assertNull($member->birth_date);
        $this->assertNull($member->getRawOriginal('birth_date'));
        $this->assertNull($member->getRawOriginal('gender'));
        $this->assertNull($member->getRawOriginal('bio'));
    }

    public function test_anggota_bisa_mengunggah_foto_profil(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(ProfileEdit::class)
            ->set('photo', UploadedFile::fake()->image('foto.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNotNull($member->fresh()->photoUrl());
    }

    public function test_persentase_kelengkapan_meningkat_setelah_profil_diisi(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);

        $members = app(MemberService::class);
        $completionBefore = $members->profileCompletionPercentage($member->fresh());

        Livewire::actingAs($user)
            ->test(ProfileEdit::class)
            ->set('gender', 'L')
            ->set('birth_place', 'Bengkalis')
            ->set('birth_date', '1990-01-01')
            ->set('institution', 'Universitas Riau')
            ->set('profession', 'Dosen')
            ->set('expertise', 'Ekonomi Syariah')
            ->set('bio', 'Bio saya')
            ->set('address', 'Jl. Contoh No. 1')
            ->call('save')
            ->assertHasNoErrors();

        $completionAfter = $members->profileCompletionPercentage($member->fresh());

        $this->assertGreaterThan($completionBefore, $completionAfter);
    }

    public function test_profil_kosong_persentase_kelengkapan_rendah(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)->test(ProfileEdit::class);

        $this->assertLessThan(50, $component->viewData('completion'));
    }
}
