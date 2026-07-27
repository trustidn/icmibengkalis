<div class="p-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Galeri</flux:heading>
        <flux:button wire:click="create" variant="primary">Album Baru</flux:button>
    </div>

    @if ($showForm)
        <flux:card class="mt-6">
            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input label="Judul Album" wire:model="title" />
                <flux:select label="Jenis" wire:model="type">
                    <option value="foto">Foto</option>
                    <option value="video">Video</option>
                </flux:select>
                <flux:textarea label="Deskripsi" wire:model="description" rows="3" />

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
            <flux:table.column>Jenis</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($albums as $album)
                <flux:table.row wire:key="album-{{ $album->id }}">
                    <flux:table.cell>{{ $album->title }}</flux:table.cell>
                    <flux:table.cell>{{ ucfirst($album->type) }}</flux:table.cell>
                    <flux:table.cell>{{ $album->is_published ? 'Tayang' : 'Draf' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $album)
                                <flux:button :href="route('admin.gallery.edit', $album)" size="sm" wire:navigate>Kelola Item</flux:button>
                                <flux:button wire:click="togglePublish({{ $album->id }})" size="sm">{{ $album->is_published ? 'Sembunyikan' : 'Tayangkan' }}</flux:button>
                            @endcan
                            @can('delete', $album)
                                <x-confirm-delete-button name="confirm-delete-album-{{ $album->id }}" wire-click="delete({{ $album->id }})" message="Hapus album ini?" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($albums as $album)
            <div wire:key="album-card-{{ $album->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold">{{ $album->title }}</p>
                    <flux:badge size="sm" class="shrink-0">{{ $album->is_published ? 'Tayang' : 'Draf' }}</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500">{{ ucfirst($album->type) }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @can('update', $album)
                        <flux:button :href="route('admin.gallery.edit', $album)" size="sm" wire:navigate>Kelola Item</flux:button>
                        <flux:button wire:click="togglePublish({{ $album->id }})" size="sm">{{ $album->is_published ? 'Sembunyikan' : 'Tayangkan' }}</flux:button>
                    @endcan
                    @can('delete', $album)
                        <x-confirm-delete-button name="confirm-delete-album-mobile-{{ $album->id }}" wire-click="delete({{ $album->id }})" message="Hapus album ini?" />
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada data.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $albums->links() }}</div>
</div>
