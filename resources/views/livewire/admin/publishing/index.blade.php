<div class="p-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Berita & Artikel</flux:heading>
        <div class="flex gap-2">
            @can('publishing.review')
                <flux:button :href="route('admin.publishing.review-queue')" wire:navigate>Antrean Review</flux:button>
            @endcan
            <flux:button :href="route('admin.publishing.create')" variant="primary" wire:navigate>Tulis Baru</flux:button>
        </div>
    </div>

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Kategori</flux:table.column>
            <flux:table.column>Penulis</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($posts as $post)
                <flux:table.row wire:key="post-{{ $post->id }}">
                    <flux:table.cell>{{ $post->title }}</flux:table.cell>
                    <flux:table.cell>{{ $post->category?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $post->author->name }}</flux:table.cell>
                    <flux:table.cell>
                        {{ $post->status->label() }}
                        @if ($post->status->value === 'rejected' && $post->review_note)
                            <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ $post->review_note }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button :href="route('admin.publishing.edit', $post)" size="sm" wire:navigate>Ubah</flux:button>

                            @if ($post->status->value === 'draft')
                                <flux:button wire:click="submitForReview({{ $post->id }})" size="sm" variant="primary">Ajukan Review</flux:button>
                            @elseif ($post->status->value === 'rejected')
                                <flux:button wire:click="revise({{ $post->id }})" size="sm">Revisi</flux:button>
                            @elseif ($post->status->value === 'published')
                                <flux:button wire:click="archive({{ $post->id }})" size="sm">Arsipkan</flux:button>
                            @endif

                            <x-confirm-delete-button name="confirm-delete-post-{{ $post->id }}" wire-click="delete({{ $post->id }})" message="Hapus post ini?" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($posts as $post)
            <div wire:key="post-card-{{ $post->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold">{{ $post->title }}</p>
                        <p class="mt-0.5 text-sm text-zinc-500">{{ $post->category?->name ?? '—' }} · {{ $post->author->name }}</p>
                    </div>
                    <flux:badge size="sm" class="shrink-0">{{ $post->status->label() }}</flux:badge>
                </div>
                @if ($post->status->value === 'rejected' && $post->review_note)
                    <flux:text size="sm" class="mt-2 text-red-600 dark:text-red-400">{{ $post->review_note }}</flux:text>
                @endif
                <div class="mt-3 flex flex-wrap gap-2">
                    <flux:button :href="route('admin.publishing.edit', $post)" size="sm" wire:navigate>Ubah</flux:button>

                    @if ($post->status->value === 'draft')
                        <flux:button wire:click="submitForReview({{ $post->id }})" size="sm" variant="primary">Ajukan Review</flux:button>
                    @elseif ($post->status->value === 'rejected')
                        <flux:button wire:click="revise({{ $post->id }})" size="sm">Revisi</flux:button>
                    @elseif ($post->status->value === 'published')
                        <flux:button wire:click="archive({{ $post->id }})" size="sm">Arsipkan</flux:button>
                    @endif

                    <x-confirm-delete-button name="confirm-delete-post-mobile-{{ $post->id }}" wire-click="delete({{ $post->id }})" message="Hapus post ini?" />
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada data.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
</div>
