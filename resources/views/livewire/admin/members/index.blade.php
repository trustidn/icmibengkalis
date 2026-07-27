<div class="p-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Anggota</flux:heading>
        <div class="flex gap-2">
            @can('members.import')
                <flux:button :href="route('admin.members.import')" wire:navigate>Impor Excel</flux:button>
            @endcan
            @can('members.create')
                <flux:button :href="route('admin.members.create')" variant="primary" wire:navigate>Tambah Anggota</flux:button>
            @endcan
        </div>
    </div>

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
            <flux:table.column>Kecamatan</flux:table.column>
            <flux:table.column>Profesi</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($members as $member)
                <flux:table.row wire:key="member-{{ $member->id }}">
                    <flux:table.cell>{{ $member->nia }}</flux:table.cell>
                    <flux:table.cell>{{ $member->full_name }}</flux:table.cell>
                    <flux:table.cell>{{ $member->district?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $member->profession ?: '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $member->status->label() }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button :href="route('admin.members.edit', $member)" size="sm" wire:navigate>Ubah</flux:button>
                            @can('members.delete')
                                <x-confirm-delete-button name="confirm-delete-member-{{ $member->id }}" wire-click="delete({{ $member->id }})" message="Hapus anggota ini?" />
                            @endcan
                        </div>
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
            <div wire:key="member-card-{{ $member->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $member->full_name }}</p>
                        <p class="mt-0.5 text-sm text-zinc-500">{{ $member->nia }}</p>
                    </div>
                    <flux:badge size="sm" class="shrink-0">{{ $member->status->label() }}</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500">{{ $member->profession ?: '—' }} @if ($member->district) · {{ $member->district->name }} @endif</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <flux:button :href="route('admin.members.edit', $member)" size="sm" wire:navigate>Ubah</flux:button>
                    @can('members.delete')
                        <x-confirm-delete-button name="confirm-delete-member-mobile-{{ $member->id }}" wire-click="delete({{ $member->id }})" message="Hapus anggota ini?" />
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada data anggota.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $members->links() }}</div>
</div>
