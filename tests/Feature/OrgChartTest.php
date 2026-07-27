<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Livewire\Public\OrgChart;
use App\Models\Member;
use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrgChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_render_periode_aktif(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true, 'name' => '2025-2030']);
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id, 'name' => 'Bidang Ekonomi']);
        $member = Member::factory()->create(['full_name' => 'Ahmad Fauzi']);
        $unit->assignments()->create(['member_id' => $member->id, 'position_title' => 'Ketua Bidang']);

        $this->get(route('org-chart.show'))
            ->assertOk()
            ->assertSee('Bidang Ekonomi')
            ->assertSee('Ahmad Fauzi');
    }

    public function test_search_menyaring_node(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true]);
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id, 'name' => 'Bidang Ekonomi']);
        $member = Member::factory()->create(['full_name' => 'Ahmad Fauzi']);
        $unit->assignments()->create(['member_id' => $member->id, 'position_title' => 'Ketua Bidang']);

        Livewire::test(OrgChart::class)
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Fauzi');
    }

    public function test_ganti_periode_menampilkan_struktur_berbeda(): void
    {
        $periodA = OrgPeriod::factory()->create(['name' => 'Periode A']);
        $periodB = OrgPeriod::factory()->create(['name' => 'Periode B']);
        OrgUnit::factory()->create(['org_period_id' => $periodA->id, 'name' => 'Unit A']);
        OrgUnit::factory()->create(['org_period_id' => $periodB->id, 'name' => 'Unit B']);

        Livewire::test(OrgChart::class)
            ->set('periodId', $periodA->id)
            ->assertSee('Unit A')
            ->assertDontSee('Unit B')
            ->set('periodId', $periodB->id)
            ->assertSee('Unit B')
            ->assertDontSee('Unit A');
    }

    public function test_daftar_anggota_tampil_setelah_struktur_terurut_jabatan_lalu_tanggal_gabung(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true]);
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id, 'sort_order' => 1]);

        $ketua = Member::factory()->create(['full_name' => 'Ketua Pengurus', 'joined_at' => now()->subYears(1), 'status' => MemberStatus::Aktif]);
        $unit->assignments()->create(['member_id' => $ketua->id, 'position_title' => 'Ketua', 'sort_order' => 1]);

        $anggotaLama = Member::factory()->create(['full_name' => 'Anggota Senior', 'joined_at' => now()->subYears(5), 'status' => MemberStatus::Aktif]);
        $anggotaBaru = Member::factory()->create(['full_name' => 'Anggota Junior', 'joined_at' => now()->subMonths(1), 'status' => MemberStatus::Aktif]);

        $response = $this->get(route('org-chart.show'));

        $response->assertOk()->assertSee('Daftar Anggota');

        $content = $response->getContent();
        $posKetua = strpos($content, 'Ketua Pengurus');
        $posSenior = strpos($content, 'Anggota Senior');
        $posJunior = strpos($content, 'Anggota Junior');

        // Pengurus (Ketua Pengurus) tampil lebih dahulu meski bergabung paling akhir.
        $this->assertLessThan($posSenior, $posKetua);
        // Di antara non-pengurus, yang lebih dulu bergabung tampil lebih dahulu.
        $this->assertLessThan($posJunior, $posSenior);
    }

    public function test_nama_pengurus_di_daftar_anggota_bertaut_ke_profil_publik(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true]);
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id]);
        $member = Member::factory()->create(['full_name' => 'Siti Aminah', 'status' => MemberStatus::Aktif]);
        $unit->assignments()->create(['member_id' => $member->id, 'position_title' => 'Sekretaris']);

        $this->get(route('org-chart.show'))
            ->assertSee(route('profiles.show', $member->slug));
    }
}
