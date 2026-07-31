<div class="p-6">
    <flux:heading size="xl">Struktur Unit — {{ $period->name }}</flux:heading>

    <flux:card class="mt-6 max-w-lg">
        <form wire:submit="addUnit" class="flex flex-col gap-3">
            <flux:input label="Nama Unit" wire:model="name" placeholder="cth: Bidang Ekonomi" />
            <flux:select label="Induk (opsional)" wire:model="parent_id">
                <option value="">— Unit utama —</option>
                @foreach ($allUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </flux:select>
            <div><flux:button type="submit" variant="primary">Tambah Unit</flux:button></div>
        </form>
    </flux:card>

    <div class="mt-6 flex flex-col gap-2">
        @foreach ($unitsByParent->get(0, collect()) as $unit)
            @include('livewire.admin.organization._unit-row', ['unit' => $unit, 'depth' => 0])
        @endforeach
    </div>
</div>
