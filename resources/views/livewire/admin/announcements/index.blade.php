<div class="p-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Pengumuman</flux:heading>
        <flux:button wire:click="create" variant="primary">Buat Pengumuman</flux:button>
    </div>

    @if ($showForm)
        <flux:card class="mt-6">
            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input label="Judul" wire:model="title" />
                <flux:textarea label="Isi" wire:model="body" rows="4" />
                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="datetime-local" label="Mulai" wire:model="starts_at" />
                    <flux:input type="datetime-local" label="Berakhir" wire:model="ends_at" />
                </div>
                <flux:checkbox wire:model="is_pinned" label="Sematkan di beranda" />

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                    <flux:button type="button" wire:click="$set('showForm', false)">Batal</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Periode</flux:table.column>
            <flux:table.column>Pin</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($announcements as $announcement)
                <flux:table.row wire:key="announcement-{{ $announcement->id }}">
                    <flux:table.cell>{{ $announcement->title }}</flux:table.cell>
                    <flux:table.cell>
                        {{ $announcement->starts_at?->format('d/m/Y') }} — {{ $announcement->ends_at?->format('d/m/Y') }}
                    </flux:table.cell>
                    <flux:table.cell>{{ $announcement->is_pinned ? 'Ya' : '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $announcement)
                                <flux:button wire:click="edit({{ $announcement->id }})" size="sm">Ubah</flux:button>
                            @endcan
                            @can('delete', $announcement)
                                <x-confirm-delete-button name="confirm-delete-announcement-{{ $announcement->id }}" wire-click="delete({{ $announcement->id }})" message="Hapus pengumuman ini?" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($announcements as $announcement)
            <div wire:key="announcement-card-{{ $announcement->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold">{{ $announcement->title }}</p>
                    @if ($announcement->is_pinned)
                        <flux:badge size="sm" class="shrink-0">Pin</flux:badge>
                    @endif
                </div>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ $announcement->starts_at?->format('d/m/Y') }} — {{ $announcement->ends_at?->format('d/m/Y') }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @can('update', $announcement)
                        <flux:button wire:click="edit({{ $announcement->id }})" size="sm">Ubah</flux:button>
                    @endcan
                    @can('delete', $announcement)
                        <x-confirm-delete-button name="confirm-delete-announcement-mobile-{{ $announcement->id }}" wire-click="delete({{ $announcement->id }})" message="Hapus pengumuman ini?" />
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada data.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $announcements->links() }}</div>
</div>
