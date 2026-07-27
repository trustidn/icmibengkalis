<div class="p-6">
    <flux:heading size="xl">Taksonomi Bidang Keahlian</flux:heading>

    <flux:card class="mt-6 max-w-md">
        <form wire:submit="save" class="flex flex-col gap-3">
            <flux:input label="Nama Bidang" wire:model="name" />
            <flux:select label="Induk (opsional)" wire:model="parent_id">
                <option value="">— Bidang utama —</option>
                @foreach ($parents as $parent)
                    @if ($parent->id !== $editingId)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endif
                @endforeach
            </flux:select>
            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Simpan' : 'Tambah' }}</flux:button>
                @if ($editingId)
                    <flux:button type="button" wire:click="cancel">Batal</flux:button>
                @endif
            </div>
        </form>
    </flux:card>

    <div class="mt-6 flex flex-col gap-2">
        @foreach ($roots as $root)
            <div wire:key="field-{{ $root->id }}">
                <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:text class="font-semibold">{{ $root->name }}</flux:text>
                    <div class="flex gap-2">
                        <flux:button wire:click="edit({{ $root->id }})" size="sm">Ubah</flux:button>
                        <x-confirm-delete-button name="confirm-delete-field-{{ $root->id }}" wire-click="delete({{ $root->id }})" message="Hapus bidang ini?" />
                    </div>
                </div>
                @if ($root->children->isNotEmpty())
                    <div class="ml-6 mt-2 flex flex-col gap-2">
                        @foreach ($root->children as $child)
                            <div wire:key="field-{{ $child->id }}" class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <flux:text>{{ $child->name }}</flux:text>
                                <div class="flex gap-2">
                                    <flux:button wire:click="edit({{ $child->id }})" size="sm">Ubah</flux:button>
                                    <x-confirm-delete-button name="confirm-delete-field-{{ $child->id }}" wire-click="delete({{ $child->id }})" message="Hapus bidang ini?" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
