<div class="p-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Arsip Digital</flux:heading>
        <div class="flex gap-2">
            <flux:button :href="route('admin.archive.categories')" wire:navigate>Kategori</flux:button>
            @can('archive.create')
                <flux:button :href="route('admin.archive.create')" variant="primary" wire:navigate>Unggah Dokumen</flux:button>
            @endcan
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.400ms="search" placeholder="Cari judul..." class="max-w-xs" />

        <flux:select wire:model.live="category_id" class="max-w-xs">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="doc_type" class="max-w-xs">
            <option value="">Semua Jenis</option>
            @foreach ($docTypes as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </flux:select>
    </div>

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Jenis</flux:table.column>
            <flux:table.column>Kategori</flux:table.column>
            <flux:table.column>Akses</flux:table.column>
            <flux:table.column>Versi</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($documents as $document)
                <flux:table.row wire:key="document-{{ $document->id }}">
                    <flux:table.cell>{{ $document->title }}</flux:table.cell>
                    <flux:table.cell>{{ $document->doc_type->label() }}</flux:table.cell>
                    <flux:table.cell>{{ $document->category?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $document->access_level->label() }}</flux:table.cell>
                    <flux:table.cell>v{{ $document->current_version }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button :href="route('admin.archive.edit', $document)" size="sm" wire:navigate>Kelola</flux:button>
                            @can('archive.delete')
                                <x-confirm-delete-button name="confirm-delete-document-{{ $document->id }}" wire-click="delete({{ $document->id }})" message="Hapus dokumen ini?" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">Belum ada dokumen.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($documents as $document)
            <div wire:key="document-card-{{ $document->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold">{{ $document->title }}</p>
                    <flux:badge size="sm" class="shrink-0">v{{ $document->current_version }}</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500">{{ $document->doc_type->label() }} · {{ $document->category?->name ?? '—' }} · {{ $document->access_level->label() }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <flux:button :href="route('admin.archive.edit', $document)" size="sm" wire:navigate>Kelola</flux:button>
                    @can('archive.delete')
                        <x-confirm-delete-button name="confirm-delete-document-mobile-{{ $document->id }}" wire-click="delete({{ $document->id }})" message="Hapus dokumen ini?" />
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada dokumen.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $documents->links() }}</div>
</div>
