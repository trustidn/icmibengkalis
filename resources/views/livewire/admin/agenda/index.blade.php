<div class="p-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Agenda</flux:heading>
        <flux:button :href="route('admin.agenda.create')" variant="primary" wire:navigate>Tambah Agenda</flux:button>
    </div>

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Waktu</flux:table.column>
            <flux:table.column>Lokasi</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($events as $event)
                <flux:table.row wire:key="event-{{ $event->id }}">
                    <flux:table.cell>{{ $event->title }}</flux:table.cell>
                    <flux:table.cell>{{ $event->starts_at->format('d/m/Y H:i') }}</flux:table.cell>
                    <flux:table.cell>{{ $event->location ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $event->is_published ? 'Tayang' : 'Draf' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $event)
                                <flux:button :href="route('admin.agenda.edit', $event)" size="sm" wire:navigate>Ubah</flux:button>
                            @endcan
                            @can('delete', $event)
                                <x-confirm-delete-button name="confirm-delete-event-{{ $event->id }}" wire-click="delete({{ $event->id }})" message="Hapus agenda ini?" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($events as $event)
            <div wire:key="event-card-{{ $event->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold">{{ $event->title }}</p>
                    <flux:badge size="sm" class="shrink-0">{{ $event->is_published ? 'Tayang' : 'Draf' }}</flux:badge>
                </div>
                <p class="mt-1 text-sm text-zinc-500">{{ $event->starts_at->format('d/m/Y H:i') }} · {{ $event->location ?? '—' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @can('update', $event)
                        <flux:button :href="route('admin.agenda.edit', $event)" size="sm" wire:navigate>Ubah</flux:button>
                    @endcan
                    @can('delete', $event)
                        <x-confirm-delete-button name="confirm-delete-event-mobile-{{ $event->id }}" wire-click="delete({{ $event->id }})" message="Hapus agenda ini?" />
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada data.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $events->links() }}</div>
</div>
