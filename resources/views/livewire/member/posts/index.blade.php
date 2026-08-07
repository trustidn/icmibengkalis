<div class="p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Artikel Saya</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Seluruh tulisan yang pernah Anda kirim, beserta statusnya.</flux:text>
        </div>
        <flux:button :href="route('member.posts.create')" variant="primary" wire:navigate>Tulis Baru</flux:button>
    </div>

    {{-- Desktop --}}
    <flux:table class="mt-6 hidden md:table">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Tanggal Publish</flux:table.column>
            <flux:table.column>Apresiasi</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($posts as $post)
                <flux:table.row wire:key="post-{{ $post->id }}">
                    <flux:table.cell>
                        <span class="font-medium">{{ $post->title }}</span>
                        <flux:text size="sm" class="block text-zinc-500">{{ $post->type->label() }}</flux:text>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $post->status->label() }}
                        @if ($post->status->value === 'rejected' && $post->review_note)
                            <flux:text size="sm" class="text-red-600 dark:text-red-400 block">{{ $post->review_note }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ ($post->published_at ?? $post->created_at)->translatedFormat('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <span class="flex items-center gap-1 text-primary font-medium">
                            <span class="material-symbols-outlined text-[15px] [font-variation-settings:'FILL'_1]">thumb_up</span>{{ $post->likes_count }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $post)
                                <flux:button :href="route('member.posts.edit', $post)" size="sm" wire:navigate>Ubah</flux:button>
                            @endcan
                            @if ($post->status->value === 'published')
                                <flux:button :href="route('posts.show', $post->slug)" size="sm" variant="ghost" wire:navigate>Lihat</flux:button>
                            @endif
                            @can('delete', $post)
                                <x-confirm-delete-button name="confirm-delete-post-{{ $post->id }}" wire-click="delete({{ $post->id }})"
                                    message="Hapus tulisan &quot;{{ $post->title }}&quot; secara permanen?" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">Anda belum pernah mengirim tulisan.</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Mobile: kartu --}}
    <div class="mt-6 flex flex-col gap-3 md:hidden">
        @forelse ($posts as $post)
            <div wire:key="post-card-{{ $post->id }}" class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-start justify-between gap-3">
                    <p class="min-w-0 font-semibold leading-snug">{{ $post->title }}</p>
                    <flux:badge size="sm" class="shrink-0">{{ $post->status->label() }}</flux:badge>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500">
                    <span>{{ $post->type->label() }}</span>
                    <span>{{ ($post->published_at ?? $post->created_at)->translatedFormat('d M Y') }}</span>
                    <span class="flex items-center gap-1 text-primary font-medium">
                        <span class="material-symbols-outlined text-[14px] [font-variation-settings:'FILL'_1]">thumb_up</span>{{ $post->likes_count }}
                    </span>
                </div>
                @if ($post->status->value === 'rejected' && $post->review_note)
                    <flux:text size="sm" class="mt-1 block text-red-600 dark:text-red-400">{{ $post->review_note }}</flux:text>
                @endif
                <div class="mt-3 flex flex-wrap gap-2">
                    @can('update', $post)
                        <flux:button :href="route('member.posts.edit', $post)" size="sm" wire:navigate>Ubah</flux:button>
                    @endcan
                    @if ($post->status->value === 'published')
                        <flux:button :href="route('posts.show', $post->slug)" size="sm" variant="ghost" wire:navigate>Lihat</flux:button>
                    @endif
                    @can('delete', $post)
                        <x-confirm-delete-button name="confirm-delete-post-mobile-{{ $post->id }}" wire-click="delete({{ $post->id }})"
                            message="Hapus tulisan &quot;{{ $post->title }}&quot; secara permanen?" />
                    @endcan
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Anda belum pernah mengirim tulisan.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
</div>
