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

    public function test_daftar_anggota_terurut_jabatan_lalu_tanggal_lahir_lalu_nama(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true]);
        $unit = OrgUnit::factory()->create(['org_period_id' => $period->id, 'sort_order' => 1]);

        $ketua = Member::factory()->create(['nia' => 'T-0001', 'full_name' => 'Zulkifli Ketua', 'birth_date' => '1995-01-01', 'status' => MemberStatus::Aktif]);
        $unit->assignments()->create(['member_id' => $ketua->id, 'position_title' => 'Ketua', 'sort_order' => 1]);

        $tua = Member::factory()->create(['nia' => 'T-0002', 'full_name' => 'Candra Sepuh', 'birth_date' => '1960-05-01', 'status' => MemberStatus::Aktif]);
        $muda = Member::factory()->create(['nia' => 'T-0003', 'full_name' => 'Ahmad Muda', 'birth_date' => '1990-05-01', 'status' => MemberStatus::Aktif]);
        $tanpaTglB = Member::factory()->create(['nia' => 'T-0004', 'full_name' => 'Budi Tanpa Tanggal', 'birth_date' => null, 'status' => MemberStatus::Aktif]);
        $tanpaTglA = Member::factory()->create(['nia' => 'T-0005', 'full_name' => 'Ani Tanpa Tanggal', 'birth_date' => null, 'status' => MemberStatus::Aktif]);

        $response = $this->get(route('org-chart.show'));
        $response->assertOk()->assertSee('Daftar Anggota');

        $content = $response->getContent();
        $pos = fn (string $nama) => strpos($content, $nama);

        // Pengurus dahulu meski paling muda.
        $this->assertLessThan($pos('Candra Sepuh'), $pos('Zulkifli Ketua'));
        // Punya tanggal lahir dahulu, tertua lebih dulu.
        $this->assertLessThan($pos('Ahmad Muda'), $pos('Candra Sepuh'));
        // Tanpa tanggal lahir tampil setelahnya, terurut abjad nama.
        $this->assertLessThan($pos('Ani Tanpa Tanggal'), $pos('Ahmad Muda'));
        $this->assertLessThan($pos('Budi Tanpa Tanggal'), $pos('Ani Tanpa Tanggal'));
    }

    public function test_urutan_jabatan_per_level_hirarki_bukan_per_cabang(): void
    {
        $period = OrgPeriod::factory()->create(['is_active' => true]);
        $rootA = OrgUnit::factory()->create(['org_period_id' => $period->id, 'sort_order' => 1]);
        $rootB = OrgUnit::factory()->create(['org_period_id' => $period->id, 'sort_order' => 2]);
        $anakA = OrgUnit::factory()->create(['org_period_id' => $period->id, 'parent_id' => $rootA->id, 'sort_order' => 1]);

        $l1a = Member::factory()->create(['nia' => 'LV-0001', 'full_name' => 'Pengurus Level Satu A', 'status' => MemberStatus::Aktif]);
        $l1b = Member::factory()->create(['nia' => 'LV-0002', 'full_name' => 'Pengurus Level Satu B', 'status' => MemberStatus::Aktif]);
        $l2 = Member::factory()->create(['nia' => 'LV-0003', 'full_name' => 'Pengurus Level Dua', 'status' => MemberStatus::Aktif]);

        $rootA->assignments()->create(['member_id' => $l1a->id, 'position_title' => 'Ketua']);
        $anakA->assignments()->create(['member_id' => $l2->id, 'position_title' => 'Ketua Sub']);
        $rootB->assignments()->create(['member_id' => $l1b->id, 'position_title' => 'Ketua B']);

        $content = $this->get(route('org-chart.show'))->getContent();
        // Batasi ke section Daftar Anggota — bagan struktur di atasnya memang
        // merender nama per cabang (depth-first).
        $daftar = substr($content, strpos($content, 'Daftar Anggota'));
        $pos = fn (string $nama) => strpos($daftar, $nama);

        // SEMUA level 1 (root A lalu root B) harus mendahului level 2,
        // meski sub-unit A berada "di dalam" cabang root A.
        $this->assertLessThan($pos('Pengurus Level Satu B'), $pos('Pengurus Level Satu A'));
        $this->assertLessThan($pos('Pengurus Level Dua'), $pos('Pengurus Level Satu B'));
    }

    public function test_daftar_anggota_load_more_12_per_batch(): void
    {
        foreach (range(1, 15) as $i) {
            Member::factory()->create(['nia' => sprintf('LM-%04d', $i), 'status' => MemberStatus::Aktif]);
        }

        Livewire::test(OrgChart::class)
            ->assertViewHas('members', fn ($members) => $members->count() === 12)
            ->assertViewHas('hasMore', true)
            ->assertSee('Muat Lebih Banyak')
            ->call('loadMore')
            ->assertViewHas('members', fn ($members) => $members->count() === 15)
            ->assertViewHas('hasMore', false);
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
