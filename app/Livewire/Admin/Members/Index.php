<?php

namespace App\Livewire\Admin\Members;

use App\Enums\EducationLevel;
use App\Models\District;
use App\Models\Member;
use App\Models\User;
use App\Services\Membership\MemberService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

/**
 * Pusat pengelolaan anggota — data keanggotaan SEKALIGUS akun tertautnya
 * (peran, reset sandi, aktif/nonaktif, hapus akun) agar admin tidak perlu
 * berpindah ke halaman Manajemen User.
 *
 * Aturan perlindungan akun mengikuti /admin/pengguna: butuh permission
 * users.manage, akun super-admin tidak tersentuh, dan tidak bisa
 * menonaktifkan/menghapus akun sendiri.
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $district_id = '';

    #[Url]
    public string $profession = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $education_level = '';

    // Kelola akun tertaut (inline per baris, key = member id)
    public ?int $editingRoleId = null;

    public string $editingRole = '';

    public ?int $resetPasswordId = null;

    public string $newPassword = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Member::class);
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function delete(int $memberId, MemberService $members): void
    {
        $this->authorize('delete', Member::class);

        $members->delete(Member::findOrFail($memberId));
    }

    /** Role yang boleh diberikan lewat UI — super-admin sengaja dikecualikan. */
    private function assignableRoles(): array
    {
        return Role::where('name', '!=', 'super-admin')->orderBy('name')->pluck('name')->all();
    }

    private function akunTarget(int $memberId): User
    {
        abort_unless(auth()->user()->can('users.manage'), 403);

        $user = Member::findOrFail($memberId)->user;
        abort_unless($user !== null, 404, 'Anggota ini belum punya akun.');

        if ($user->hasRole('super-admin')) {
            $this->addError('protected', 'Akun super-admin terlindungi — tidak dapat diubah dari sini.');
            abort(403, 'Akun super-admin terlindungi.');
        }

        return $user;
    }

    public function startEditRole(int $memberId): void
    {
        $user = $this->akunTarget($memberId);

        $this->reset(['resetPasswordId', 'newPassword']);
        $this->editingRoleId = $memberId;
        $this->editingRole = $user->roles->first()?->name ?? 'anggota';
    }

    public function saveRole(): void
    {
        $user = $this->akunTarget((int) $this->editingRoleId);

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

    public function startResetPassword(int $memberId): void
    {
        $this->akunTarget($memberId);

        $this->reset(['editingRoleId', 'editingRole']);
        $this->resetPasswordId = $memberId;
        $this->newPassword = '';
    }

    public function saveNewPassword(): void
    {
        $user = $this->akunTarget((int) $this->resetPasswordId);

        $validated = $this->validate([
            'newPassword' => ['required', 'string', 'min:8'],
        ], [], ['newPassword' => 'sandi baru']);

        $user->forceFill([
            'password' => Hash::make($validated['newPassword']),
            // Putus juga cookie "ingat saya" perangkat lama.
            'remember_token' => Str::random(60),
        ])->save();

        $this->reset(['resetPasswordId', 'newPassword']);

        session()->flash('members.saved', "Sandi {$user->name} berhasil direset.");
    }

    public function cancelResetPassword(): void
    {
        $this->reset(['resetPasswordId', 'newPassword']);
    }

    public function toggleUserActive(int $memberId): void
    {
        $user = $this->akunTarget($memberId);

        if ($user->id === auth()->id()) {
            $this->addError('protected', 'Anda tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function deleteUserAccount(int $memberId): void
    {
        $user = $this->akunTarget($memberId);

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

        session()->flash('members.saved', 'Akun dihapus. Data anggota tetap tersimpan.');
    }

    public function render(MemberService $members)
    {
        return view('livewire.admin.members.index', [
            'members' => $members->paginate([
                'search' => $this->search,
                'district_id' => $this->district_id ?: null,
                'profession' => $this->profession ?: null,
                'status' => $this->status ?: null,
                'education_level' => $this->education_level ?: null,
            ]),
            'districts' => District::orderBy('name')->get(),
            'educationLevels' => EducationLevel::cases(),
            'assignableRoles' => $this->assignableRoles(),
            'canManageAccounts' => auth()->user()->can('users.manage'),
        ]);
    }
}
