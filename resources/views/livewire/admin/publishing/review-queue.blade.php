<div class="p-6">
    <flux:heading size="xl">Antrean Review Publikasi</flux:heading>

    <div class="mt-6 flex gap-2 border-b border-zinc-200 dark:border-zinc-700">
        @foreach (['menunggu' => 'Menunggu', 'terjadwal' => 'Terjadwal', 'ditolak' => 'Ditolak'] as $key => $label)
            <button
                type="button"
                wire:click="$set('tab', '{{ $key }}')"
                class="border-b-2 px-3 py-2 text-sm font-medium {{ $tab === $key ? 'border-accent text-accent-content' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Jenis</flux:table.column>
            <flux:table.column>Penulis</flux:table.column>
            <flux:table.column>Diajukan</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($posts as $post)
                <flux:table.row wire:key="post-{{ $post->id }}">
                    <flux:table.cell>
                        <a href="{{ route('admin.publishing.edit', $post) }}" wire:navigate class="hover:underline">{{ $post->title }}</a>
                    </flux:table.cell>
                    <flux:table.cell>{{ $post->type->label() }}</flux:table.cell>
                    <flux:table.cell>{{ $post->author->name }}</flux:table.cell>
                    <flux:table.cell>{{ $post->updated_at->diffForHumans() }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($tab === 'menunggu')
                            <div class="flex flex-wrap gap-2">
                                <flux:button wire:click="approve({{ $post->id }})" size="sm" variant="primary">Setujui & Terbitkan</flux:button>

                                <flux:modal.trigger name="schedule-{{ $post->id }}">
                                    <flux:button size="sm">Jadwalkan</flux:button>
                                </flux:modal.trigger>

                                <flux:button wire:click="startReject({{ $post->id }})" size="sm" variant="danger">Tolak</flux:button>
                            </div>

                            <flux:modal name="schedule-{{ $post->id }}" class="max-w-sm">
                                <form wire:submit="schedule({{ $post->id }})" class="flex flex-col gap-3">
                                    <flux:heading size="lg">Jadwalkan Publikasi</flux:heading>
                                    <flux:input type="datetime-local" label="Tayang pada" wire:model="scheduledAt" />
                                    <flux:button type="submit" variant="primary">Jadwalkan</flux:button>
                                </form>
                            </flux:modal>
                        @elseif ($tab === 'ditolak')
                            <flux:text size="sm" class="text-zinc-500">{{ $post->review_note }}</flux:text>
                        @else
                            <flux:text size="sm" class="text-zinc-500">Tayang {{ $post->published_at->translatedFormat('d F Y, H:i') }}</flux:text>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">Tidak ada post di tab ini.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($posts as $post)
            <div wire:key="post-card-{{ $post->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <a href="{{ route('admin.publishing.edit', $post) }}" wire:navigate class="font-semibold hover:underline">{{ $post->title }}</a>
                <p class="mt-1 text-sm text-zinc-500">{{ $post->type->label() }} · {{ $post->author->name }} · {{ $post->updated_at->diffForHumans() }}</p>

                @if ($tab === 'menunggu')
                    <div class="mt-3 flex flex-wrap gap-2">
                        <flux:button wire:click="approve({{ $post->id }})" size="sm" variant="primary">Setujui & Terbitkan</flux:button>

                        <flux:modal.trigger name="schedule-mobile-{{ $post->id }}">
                            <flux:button size="sm">Jadwalkan</flux:button>
                        </flux:modal.trigger>

                        <flux:button wire:click="startReject({{ $post->id }})" size="sm" variant="danger">Tolak</flux:button>
                    </div>

                    <flux:modal name="schedule-mobile-{{ $post->id }}" class="max-w-sm">
                        <form wire:submit="schedule({{ $post->id }})" class="flex flex-col gap-3">
                            <flux:heading size="lg">Jadwalkan Publikasi</flux:heading>
                            <flux:input type="datetime-local" label="Tayang pada" wire:model="scheduledAt" />
                            <flux:button type="submit" variant="primary">Jadwalkan</flux:button>
                        </form>
                    </flux:modal>
                @elseif ($tab === 'ditolak')
                    <flux:text size="sm" class="mt-2 block text-zinc-500">{{ $post->review_note }}</flux:text>
                @else
                    <flux:text size="sm" class="mt-2 block text-zinc-500">Tayang {{ $post->published_at->translatedFormat('d F Y, H:i') }}</flux:text>
                @endif
            </div>
        @empty
            <p class="text-zinc-500">Tidak ada post di tab ini.</p>
        @endforelse
    </div>

    @if ($rejectingId)
        <flux:card class="mt-6 max-w-md">
            <form wire:submit="reject" class="flex flex-col gap-3">
                <flux:heading size="lg">Tolak Post</flux:heading>
                <flux:textarea label="Catatan penolakan" wire:model="rejectNote" rows="4" />
                <div class="flex gap-3">
                    <flux:button type="submit" variant="danger">Tolak</flux:button>
                    <flux:button type="button" wire:click="$set('rejectingId', null)">Batal</flux:button>
                </div>
            </form>
        </flux:card>
    @endif
</div>
