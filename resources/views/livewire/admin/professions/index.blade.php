<div class="p-6">
    <flux:heading size="xl">Profesi</flux:heading>

    <flux:card class="mt-6 max-w-md">
        <form wire:submit="save" class="flex items-end gap-3">
            <flux:input label="Nama Profesi" wire:model="name" class="flex-1" />
            <flux:button type="submit" variant="primary">{{ $editingId ? 'Simpan' : 'Tambah' }}</flux:button>
            @if ($editingId)
                <flux:button type="button" wire:click="cancel">Batal</flux:button>
            @endif
        </form>
    </flux:card>

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($professions as $profession)
                <flux:table.row wire:key="profession-{{ $profession->id }}">
                    <flux:table.cell>{{ $profession->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button wire:click="edit({{ $profession->id }})" size="sm">Ubah</flux:button>
                            <x-confirm-delete-button name="confirm-delete-profession-{{ $profession->id }}" wire-click="delete({{ $profession->id }})" message="Hapus profesi ini?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($professions as $profession)
            <div wire:key="profession-card-{{ $profession->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <p class="font-semibold">{{ $profession->name }}</p>
                <div class="flex shrink-0 gap-2">
                    <flux:button wire:click="edit({{ $profession->id }})" size="sm">Ubah</flux:button>
                    <x-confirm-delete-button name="confirm-delete-profession-mobile-{{ $profession->id }}" wire-click="delete({{ $profession->id }})" message="Hapus profesi ini?" />
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada data.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $professions->links() }}</div>
</div>
