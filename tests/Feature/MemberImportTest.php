<?php

namespace Tests\Feature;

use App\Livewire\Admin\Members\Import as MembersImport;
use App\Models\District;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class MemberImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function makeCsv(string $csv): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('anggota.csv', $csv);
    }

    public function test_import_file_valid_menambah_anggota(): void
    {
        District::factory()->create(['name' => 'Bengkalis']);

        $admin = User::factory()->create();
        $admin->givePermissionTo(['members.view', 'members.import']);

        $csv = "nama_lengkap,jenis_kelamin,kecamatan,profesi\n".
            "Ahmad Fauzi,L,Bengkalis,Dosen\n".
            "Siti Aminah,P,Bengkalis,Guru\n";

        Livewire::actingAs($admin)
            ->test(MembersImport::class)
            ->set('file', $this->makeCsv($csv))
            ->call('import');

        $this->assertDatabaseCount('members', 2);
        $this->assertDatabaseHas('members', ['full_name' => 'Ahmad Fauzi']);
    }

    public function test_import_dengan_baris_invalid_dilaporkan_dan_tidak_dibuat(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(['members.view', 'members.import']);

        $csv = "nama_lengkap,jenis_kelamin,kecamatan\n".
            "Valid Orang,L,\n".
            ",L,\n";

        $component = Livewire::actingAs($admin)
            ->test(MembersImport::class)
            ->set('file', $this->makeCsv($csv))
            ->call('import');

        $this->assertDatabaseCount('members', 1);
        $this->assertSame(1, $component->get('imported'));
        $this->assertCount(1, $component->get('errors_list'));
    }

    public function test_user_tanpa_permission_ditolak_akses_impor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.members.import'))
            ->assertForbidden();
    }
}
