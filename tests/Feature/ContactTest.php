<?php

namespace Tests\Feature;

use App\Livewire\Public\Contact\Form;
use App\Models\ContactMessage;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_publik_bisa_mengirim_pesan_kontak(): void
    {
        Livewire::test(Form::class)
            ->set('name', 'Budi')
            ->set('email', 'budi@example.com')
            ->set('subject', 'Pertanyaan')
            ->set('message', 'Halo, saya ingin bertanya.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contact_messages', ['email' => 'budi@example.com']);
    }

    public function test_validasi_gagal_untuk_email_tidak_valid(): void
    {
        Livewire::test(Form::class)
            ->set('name', 'Budi')
            ->set('email', 'bukan-email')
            ->set('subject', 'Pertanyaan')
            ->set('message', 'Halo')
            ->call('submit')
            ->assertHasErrors(['email']);
    }

    public function test_user_tanpa_permission_ditolak_akses_inbox(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.contact.index'))
            ->assertForbidden();
    }

    public function test_admin_dengan_permission_bisa_melihat_inbox(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('contact.view');
        ContactMessage::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.contact.index'))
            ->assertOk();
    }
}
