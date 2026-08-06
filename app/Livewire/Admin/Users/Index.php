<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

/**
 * Manajemen user untuk admin sistem (permission users.manage — super-admin & admin-web).
 *
 * Aturan perlindungan (ditegakkan server-side di setiap aksi):
 * - User ber-role super-admin TIDAK bisa diubah role-nya, dinonaktifkan, atau dihapus
 *   lewat UI ini — dan role super-admin tidak pernah ditawarkan sebagai pilihan.
 *   Penetapan super-admin hanya lewat seeder/console (by design).
 * - User tidak bisa menonaktifkan/menghapus dirinya sendiri.
 * - User yang punya artikel tidak bisa dihapus (FK posts cascade — konten ikut
 *   terhapus); nonaktifkan saja.
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // Form tambah user
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'anggota';

    // Ubah role inline
    public ?int $editingRoleId = null;

    public string $editingRole = '';

    // Reset sandi inline
    public ?int $resetPasswordId = null;

    public string $newPassword = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('users.manage'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /** Role yang boleh diberikan lewat UI — super-admin sengaja dikecualikan. */
    private function assignableRoles(): array
    {
        return Role::where('name', '!=', 'super-admin')->orderBy('name')->pluck('name')->all();
    }

    private function guardTarget(User $user): void
    {
        abort_unless(auth()->user()->can('users.manage'), 403);

        if ($user->hasRole('super-admin')) {
            $this->addError('protected', 'Akun super-admin terlindungi — tidak dapat diubah atau dihapus dari sini.');
            abort(403, 'Akun super-admin terlindungi.');
        }
    }

    public function createUser(): void
    {
        abort_unless(auth()->user()->can('users.manage'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:'.implode(',', $this->assignableRoles())],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($validated['role']);

        $this->reset(['name', 'email', 'password']);
        $this->role = 'anggota';

        session()->flash('users.saved', "User {$user->name} dibuat.");
    }

    public function startEditRole(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->guardTarget($user);

        $this->editingRoleId = $user->id;
        $this->editingRole = $user->roles->first()?->name ?? 'anggota';
    }

    public function saveRole(): void
    {
        $user = User::findOrFail($this->editingRoleId);
        $this->guardTarget($user);

        $validated = $this->validate([
            'editingRole' => ['required', 'in:'.implode(',', $this->assignableRoles())],
        ]);

        $user->syncRoles($validated['editingRole']);

        $this->reset(['editingRoleId', 'editingRole']);
    }

    public function cancelEditRole(): void
    {
        $this->reset(['editingRoleId', 'editingRole']);
    }

    public function startResetPassword(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->guardTarget($user);

        $this->resetPasswordId = $user->id;
        $this->newPassword = '';
    }

    public function saveNewPassword(): void
    {
        $user = User::findOrFail($this->resetPasswordId);
        $this->guardTarget($user);

        $validated = $this->validate([
            'newPassword' => ['required', 'string', 'min:8'],
        ], [], ['newPassword' => 'sandi baru']);

        $user->forceFill([
            'password' => Hash::make($validated['newPassword']),
            // Putus juga cookie "ingat saya" perangkat lama.
            'remember_token' => Str::random(60),
        ])->save();

        $this->reset(['resetPasswordId', 'newPassword']);

        session()->flash('users.saved', "Sandi {$user->name} berhasil direset.");
    }

    public function cancelResetPassword(): void
    {
        $this->reset(['resetPasswordId', 'newPassword']);
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->guardTarget($user);

        if ($user->id === auth()->id()) {
            $this->addError('protected', 'Anda tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->guardTarget($user);

        if ($user->id === auth()->id()) {
            $this->addError('protected', 'Anda tidak dapat menghapus akun sendiri.');

            return;
        }

        if ($user->posts()->exists()) {
            $this->addError('protected', "User {$user->name} memiliki artikel — menghapusnya akan ikut menghapus artikel tersebut. Nonaktifkan saja akunnya.");

            return;
        }

        // members.user_id nullOnDelete: data anggota tetap ada, hanya tautan akunnya lepas.
        $user->delete();
    }

    public function render()
    {
        $users = User::query()
            ->with(['roles', 'member'])
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.users.index', [
            'users' => $users,
            'assignableRoles' => $this->assignableRoles(),
        ]);
    }
}
