<div class="flex flex-col gap-6 p-6">
    <div>
        <flux:heading size="xl">Manajemen User</flux:heading>
        <flux:text class="mt-1">Kelola akun, peran, dan status akses sistem. Akun super-admin terlindungi — tidak dapat diubah atau dihapus dari halaman ini.</flux:text>
    </div>

    @error('protected')
        <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ $message }}" />
    @enderror

    @if (session('users.saved'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('users.saved') }}" />
    @endif

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <flux:card class="w-full lg:w-1/3">
            <flux:heading size="lg">Tambah User</flux:heading>
            <form wire:submit="createUser" class="mt-4 flex flex-col gap-4">
                <flux:input label="Nama" wire:model="name" required />
                <flux:input label="Email" type="email" wire:model="email" required />
                <flux:input label="Password" type="password" wire:model="password" required
                            description="Minimal 8 karakter. Sampaikan ke pemilik akun untuk diganti setelah login pertama." />
                <flux:select label="Peran" wire:model="role">
                    @foreach ($assignableRoles as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </flux:select>
                <div><flux:button type="submit" variant="primary">Buat User</flux:button></div>
            </form>
        </flux:card>

        <flux:card class="w-full lg:w-2/3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading size="lg">Daftar User</flux:heading>
                <flux:input wire:model.live.debounce.400ms="search" placeholder="Cari nama/email..." class="max-w-xs" />
            </div>

            {{-- Desktop --}}
            <table class="mt-4 hidden w-full text-left text-sm md:table">
                <thead>
                    <tr class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-700">
                        <th class="py-2 pe-3 font-medium">Nama</th>
                        <th class="py-2 pe-3 font-medium">Email</th>
                        <th class="py-2 pe-3 font-medium">Peran</th>
                        <th class="py-2 pe-3 font-medium">Status</th>
                        <th class="py-2 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @php $protected = $user->hasRole('super-admin'); @endphp
                        <tr wire:key="user-{{ $user->id }}" class="border-b border-zinc-100 dark:border-zinc-800">
                            <td class="py-3 pe-3">
                                <span class="font-medium">{{ $user->name }}</span>
                                @if ($user->member)
                                    <flux:text size="sm" class="block text-zinc-500">Anggota: {{ $user->member->nia }}</flux:text>
                                @endif
                            </td>
                            <td class="py-3 pe-3">{{ $user->email }}</td>
                            <td class="py-3 pe-3">
                                @if ($protected)
                                    <flux:badge size="sm" color="amber" icon="lock-closed">super-admin</flux:badge>
                                @elseif ($editingRoleId === $user->id)
                                    <form wire:submit="saveRole" class="flex items-center gap-2">
                                        <flux:select wire:model="editingRole" size="sm">
                                            @foreach ($assignableRoles as $r)
                                                <option value="{{ $r }}">{{ $r }}</option>
                                            @endforeach
                                        </flux:select>
                                        <flux:button type="submit" variant="primary" size="sm">Simpan</flux:button>
                                        <flux:button type="button" size="sm" wire:click="cancelEditRole">Batal</flux:button>
                                    </form>
                                @else
                                    {{ $user->roles->pluck('name')->join(', ') ?: '—' }}
                                @endif
                            </td>
                            <td class="py-3 pe-3">
                                @if ($user->is_active)
                                    <flux:badge size="sm" color="green">Aktif</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">Nonaktif</flux:badge>
                                @endif
                            </td>
                            <td class="py-3">
                                @unless ($protected)
                                    @if ($resetPasswordId === $user->id)
                                        <form wire:submit="saveNewPassword" class="flex flex-wrap items-center gap-2">
                                            <flux:input type="text" wire:model="newPassword" size="sm" placeholder="Sandi baru (min. 8)" class="max-w-44" />
                                            <flux:button type="submit" variant="primary" size="sm">Simpan Sandi</flux:button>
                                            <flux:button type="button" size="sm" wire:click="cancelResetPassword">Batal</flux:button>
                                        </form>
                                        @error('newPassword') <flux:text size="sm" class="mt-1 block text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            <flux:button size="sm" wire:click="startEditRole({{ $user->id }})">Ubah Peran</flux:button>
                                            <flux:button size="sm" wire:click="startResetPassword({{ $user->id }})">Reset Sandi</flux:button>
                                            <flux:button size="sm" wire:click="toggleActive({{ $user->id }})">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</flux:button>
                                            <x-confirm-delete-button name="confirm-delete-user-{{ $user->id }}" wire-click="deleteUser({{ $user->id }})"
                                                message="Hapus user {{ $user->name }}? Data anggota terkait tidak ikut terhapus, hanya tautan akunnya." />
                                        </div>
                                    @endif
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Mobile --}}
            <div class="mt-4 flex flex-col gap-3 md:hidden">
                @foreach ($users as $user)
                    @php $protected = $user->hasRole('super-admin'); @endphp
                    <div wire:key="user-mobile-{{ $user->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-medium">{{ $user->name }}</p>
                                <flux:text size="sm" class="text-zinc-500">{{ $user->email }}</flux:text>
                            </div>
                            @if ($user->is_active)
                                <flux:badge size="sm" color="green">Aktif</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc">Nonaktif</flux:badge>
                            @endif
                        </div>
                        <div class="mt-2">
                            @if ($protected)
                                <flux:badge size="sm" color="amber" icon="lock-closed">super-admin</flux:badge>
                            @elseif ($editingRoleId === $user->id)
                                <form wire:submit="saveRole" class="flex items-center gap-2">
                                    <flux:select wire:model="editingRole" size="sm">
                                        @foreach ($assignableRoles as $r)
                                            <option value="{{ $r }}">{{ $r }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:button type="submit" variant="primary" size="sm">Simpan</flux:button>
                                    <flux:button type="button" size="sm" wire:click="cancelEditRole">Batal</flux:button>
                                </form>
                            @else
                                <flux:text size="sm">Peran: {{ $user->roles->pluck('name')->join(', ') ?: '—' }}</flux:text>
                            @endif
                        </div>
                        @unless ($protected)
                            @if ($resetPasswordId === $user->id)
                                <form wire:submit="saveNewPassword" class="mt-3 flex flex-wrap items-center gap-2">
                                    <flux:input type="text" wire:model="newPassword" size="sm" placeholder="Sandi baru (min. 8)" />
                                    <flux:button type="submit" variant="primary" size="sm">Simpan Sandi</flux:button>
                                    <flux:button type="button" size="sm" wire:click="cancelResetPassword">Batal</flux:button>
                                </form>
                                @error('newPassword') <flux:text size="sm" class="mt-1 block text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                            @else
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <flux:button size="sm" wire:click="startEditRole({{ $user->id }})">Ubah Peran</flux:button>
                                    <flux:button size="sm" wire:click="startResetPassword({{ $user->id }})">Reset Sandi</flux:button>
                                    <flux:button size="sm" wire:click="toggleActive({{ $user->id }})">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</flux:button>
                                    <x-confirm-delete-button name="confirm-delete-user-mobile-{{ $user->id }}" wire-click="deleteUser({{ $user->id }})"
                                        message="Hapus user {{ $user->name }}? Data anggota terkait tidak ikut terhapus, hanya tautan akunnya." />
                                </div>
                            @endif
                        @endunless
                    </div>
                @endforeach
            </div>

            <div class="mt-4">{{ $users->links() }}</div>
        </flux:card>
    </div>
</div>
