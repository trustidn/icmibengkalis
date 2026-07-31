{{-- Baris unit rekursif — dipakai unit-tree.blade.php. Scope Livewire ($renamingId,
     $renamingName, $unitsByParent) diwariskan otomatis oleh @include. --}}
<div wire:key="unit-{{ $unit->id }}">
    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
        @if ($renamingId === $unit->id)
            <form wire:submit="saveRename" class="flex flex-1 items-center gap-2">
                <flux:input wire:model="renamingName" class="flex-1" autofocus />
                <flux:button type="submit" variant="primary" size="sm">Simpan</flux:button>
                <flux:button type="button" size="sm" wire:click="cancelRename">Batal</flux:button>
            </form>
        @else
            <flux:text @class(['font-semibold' => $depth === 0])>{{ $unit->name }}</flux:text>
            <div class="flex flex-wrap gap-2">
                <flux:button size="sm" wire:click="startRename({{ $unit->id }})">Ganti Nama</flux:button>
                <flux:button :href="route('admin.organization.assignments', $unit)" size="sm" wire:navigate>Kelola Penugasan</flux:button>
                <x-confirm-delete-button name="confirm-delete-unit-{{ $unit->id }}" wire-click="deleteUnit({{ $unit->id }})" message="Hapus unit ini beserta seluruh sub-unitnya?" />
            </div>
        @endif
    </div>

    @if ($unitsByParent->has($unit->id))
        <div class="ml-6 mt-2 flex flex-col gap-2">
            @foreach ($unitsByParent[$unit->id] as $child)
                @include('livewire.admin.organization._unit-row', ['unit' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
