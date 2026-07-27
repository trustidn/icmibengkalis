<div class="mx-auto max-w-2xl p-6">
    <flux:heading size="xl">{{ $event ? 'Ubah Agenda' : 'Tambah Agenda' }}</flux:heading>

    <form wire:submit="save" class="mt-6 flex flex-col gap-4">
        <flux:input label="Judul" wire:model="title" />
        <flux:textarea label="Deskripsi" wire:model="description" rows="4" />
        <flux:input label="Lokasi" wire:model="location" />

        <div class="grid grid-cols-2 gap-4">
            <flux:input type="datetime-local" label="Mulai" wire:model="starts_at" />
            <flux:input type="datetime-local" label="Selesai" wire:model="ends_at" />
        </div>

        <flux:checkbox wire:model="is_published" label="Tayangkan di halaman publik" />

        <div>
            <flux:button type="submit" variant="primary">Simpan</flux:button>
        </div>
    </form>
</div>
