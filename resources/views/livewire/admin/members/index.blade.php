<div class="p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Anggota</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Data keanggotaan sekaligus akun tertautnya — peran, sandi, status akses. Terurut dari yang terbaru.</flux:text>
        </div>
        <div class="flex gap-2">
            @can('members.import')
                <flux:button :href="route('admin.members.import')" wire:navigate>Impor Excel</flux:button>
            @endcan
            @can('members.create')
                <flux:button :href="route('admin.members.create')" variant="primary" wire:navigate>Tambah Anggota</flux:button>
            @endcan
        </div>
    </div>

    @error('protected')
        <flux:callout class="mt-4" variant="danger" icon="exclamation-triangle" heading="{{ $message }}" />
    @enderror

    @if (session('members.saved'))
        <flux:callout class="mt-4" variant="success" icon="check-circle" heading="{{ session('members.saved') }}" />
    @endif

    <div class="mt-6 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Cari nama / NIA..." class="max-w-xs" />

        <flux:select wire:model.live="district_id" class="max-w-xs">
            <option value="">Semua Kecamatan</option>
            @foreach ($districts as $district)
                <option value="{{ $district->id }}">{{ $district->name }}</option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live.debounce.400ms="profession" placeholder="Cari profesi..." class="max-w-xs" />

        <flux:select wire:model.live="status" class="max-w-xs">
            <option value="">Semua Status</option>
            <option value="aktif">Aktif</option>
            <option value="tidak_aktif">Tidak Aktif</option>
            <option value="alumni">Alumni</option>
            <option value="meninggal">Meninggal</option>
        </flux:select>

        <flux:select wire:model.live="education_level" class="max-w-xs">
            <option value="">Semua Pendidikan</option>
            @foreach ($educationLevels as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>NIA</flux:table.column>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Profesi</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Akun</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($members as $member)
                @php $akun = $member->user; $terlindungi = $akun?->hasRole('super-admin'); @endphp
                <flux:table.row wire:key="member-{{ $member->id }}">
                    <flux:table.cell>{{ $member->nia }}</flux:table.cell>
                    <flux:table.cell>
                        <span class="font-medium">{{ $member->full_name }}</span>
                        @if ($akun)
                            <flux:text size="sm" class="block text-zinc-500">{{ $akun->email }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $member->profession ?: '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $member->status->label() }}</flux:table.cell>
                    <flux:table.cell>
                        @if (! $akun)
                            <flux:badge size="sm" color="zinc">Tanpa akun</flux:badge>
                        @elseif ($terlindungi)
                            <flux:badge size="sm" color="amber" icon="lock-closed">super-admin</flux:badge>
                        @else
                            <div class="flex flex-wrap items-center gap-1.5">
                                <flux:badge size="sm">{{ $akun->roles->pluck('name')->join(', ') ?: '—' }}</flux:badge>
                                <flux:badge size="sm" :color="$akun->is_active ? 'green' : 'zinc'">{{ $akun->is_active ? 'Aktif' : 'Nonaktif' }}</flux:badge>
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($canManageAccounts && $akun && ! $terlindungi && $editingRoleId === $member->id)
                            <form wire:submit="saveRole" class="flex items-center gap-2">
                                <flux:select wire:model="editingRole" size="sm">
                                    @foreach ($assignableRoles as $r)
                                        <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </flux:select>
                                <flux:button type="submit" variant="primary" size="sm">Simpan</flux:button>
                                <flux:button type="button" size="sm" wire:click="cancelEditRole">Batal</flux:button>
                            </form>
                        @elseif ($canManageAccounts && $akun && ! $terlindungi && $resetPasswordId === $member->id)
                            <form wire:submit="saveNewPassword" class="flex flex-wrap items-center gap-2">
                                <flux:input type="text" wire:model="newPassword" size="sm" placeholder="Sandi baru (min. 8)" class="max-w-44" />
                                <flux:button type="submit" variant="primary" size="sm">Simpan</flux:button>
                                <flux:button type="button" size="sm" wire:click="cancelResetPassword">Batal</flux:button>
                            </form>
                            @error('newPassword') <flux:text size="sm" class="mt-1 block text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                        @else
                            <div class="flex flex-wrap gap-2">
                                <flux:button :href="route('admin.members.edit', $member)" size="sm" wire:navigate>Ubah</flux:button>
                                @if ($canManageAccounts && $akun && ! $terlindungi)
                                    <flux:button wire:click="startEditRole({{ $member->id }})" size="sm">Peran</flux:button>
                                    <flux:button wire:click="startResetPassword({{ $member->id }})" size="sm">Reset Sandi</flux:button>
                                    <flux:button wire:click="toggleUserActive({{ $member->id }})" size="sm">{{ $akun->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</flux:button>
                                    <x-confirm-delete-button name="confirm-delete-account-{{ $member->id }}" wire-click="deleteUserAccount({{ $member->id }})"
                                        label="Hapus Akun" message="Hapus AKUN {{ $member->full_name }}? Data anggotanya tetap tersimpan, hanya akses loginnya yang dicabut." />
                                @endif
                                @can('members.delete')
                                    <x-confirm-delete-button name="confirm-delete-member-{{ $member->id }}" wire-click="delete({{ $member->id }})" message="Hapus DATA ANGGOTA {{ $member->full_name }} beserta profil publiknya?" />
                                @endcan
                            </div>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">Belum ada data anggota.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($members as $member)
            @php $akun = $member->user; $terlindungi = $akun?->hasRole('super-admin'); @endphp
            <div wire:key="member-card-{{ $member->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $member->full_name }}</p>
                        <p class="mt-0.5 text-sm text-zinc-500">{{ $member->nia }}@if ($akun) · {{ $akun->email }}@endif</p>
                    </div>
                    <flux:badge size="sm" class="shrink-0">{{ $member->status->label() }}</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500">{{ $member->profession ?: '—' }} @if ($member->district) · {{ $member->district->name }} @endif</p>

                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    @if (! $akun)
                        <flux:badge size="sm" color="zinc">Tanpa akun</flux:badge>
                    @elseif ($terlindungi)
                        <flux:badge size="sm" color="amber" icon="lock-closed">super-admin</flux:badge>
                    @else
                        <flux:badge size="sm">{{ $akun->roles->pluck('name')->join(', ') ?: '—' }}</flux:badge>
                        <flux:badge size="sm" :color="$akun->is_active ? 'green' : 'zinc'">{{ $akun->is_active ? 'Aktif' : 'Nonaktif' }}</flux:badge>
                    @endif
                </div>

                @if ($canManageAccounts && $akun && ! $terlindungi && $editingRoleId === $member->id)
                    <form wire:submit="saveRole" class="mt-3 flex items-center gap-2">
                        <flux:select wire:model="editingRole" size="sm">
                            @foreach ($assignableRoles as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </flux:select>
                        <flux:button type="submit" variant="primary" size="sm">Simpan</flux:button>
                        <flux:button type="button" size="sm" wire:click="cancelEditRole">Batal</flux:button>
                    </form>
                @elseif ($canManageAccounts && $akun && ! $terlindungi && $resetPasswordId === $member->id)
                    <form wire:submit="saveNewPassword" class="mt-3 flex flex-wrap items-center gap-2">
                        <flux:input type="text" wire:model="newPassword" size="sm" placeholder="Sandi baru (min. 8)" />
                        <flux:button type="submit" variant="primary" size="sm">Simpan</flux:button>
                        <flux:button type="button" size="sm" wire:click="cancelResetPassword">Batal</flux:button>
                    </form>
                    @error('newPassword') <flux:text size="sm" class="mt-1 block text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror
                @else
                    <div class="mt-3 flex flex-wrap gap-2">
                        <flux:button :href="route('admin.members.edit', $member)" size="sm" wire:navigate>Ubah</flux:button>
                        @if ($canManageAccounts && $akun && ! $terlindungi)
                            <flux:button wire:click="startEditRole({{ $member->id }})" size="sm">Peran</flux:button>
                            <flux:button wire:click="startResetPassword({{ $member->id }})" size="sm">Reset Sandi</flux:button>
                            <flux:button wire:click="toggleUserActive({{ $member->id }})" size="sm">{{ $akun->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</flux:button>
                            <x-confirm-delete-button name="confirm-delete-account-mobile-{{ $member->id }}" wire-click="deleteUserAccount({{ $member->id }})"
                                label="Hapus Akun" message="Hapus AKUN {{ $member->full_name }}? Data anggotanya tetap tersimpan, hanya akses loginnya yang dicabut." />
                        @endif
                        @can('members.delete')
                            <x-confirm-delete-button name="confirm-delete-member-mobile-{{ $member->id }}" wire-click="delete({{ $member->id }})" message="Hapus DATA ANGGOTA {{ $member->full_name }} beserta profil publiknya?" />
                        @endcan
                    </div>
                @endif
            </div>
        @empty
            <p class="text-zinc-500">Belum ada data anggota.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $members->links() }}</div>
</div>
