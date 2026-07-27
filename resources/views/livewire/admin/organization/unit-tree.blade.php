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
        @foreach ($units as $unit)
            <div wire:key="unit-{{ $unit->id }}">
                <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:text class="font-semibold">{{ $unit->name }}</flux:text>
                    <div class="flex gap-2">
                        <flux:button :href="route('admin.organization.assignments', $unit)" size="sm" wire:navigate>Kelola Penugasan</flux:button>
                        <x-confirm-delete-button name="confirm-delete-unit-{{ $unit->id }}" wire-click="deleteUnit({{ $unit->id }})" message="Hapus unit ini beserta sub-unit?" />
                    </div>
                </div>
                @if ($unit->children->isNotEmpty())
                    <div class="ml-6 mt-2 flex flex-col gap-2">
                        @foreach ($unit->children as $child)
                            <div wire:key="unit-{{ $child->id }}" class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <flux:text>{{ $child->name }}</flux:text>
                                <div class="flex gap-2">
                                    <flux:button :href="route('admin.organization.assignments', $child)" size="sm" wire:navigate>Kelola Penugasan</flux:button>
                                    <x-confirm-delete-button name="confirm-delete-unit-{{ $child->id }}" wire-click="deleteUnit({{ $child->id }})" message="Hapus unit ini?" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
