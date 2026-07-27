<div class="p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Artikel Saya</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Seluruh tulisan yang pernah Anda kirim, beserta statusnya.</flux:text>
        </div>
        <flux:button :href="route('member.posts.create')" variant="primary" wire:navigate>Tulis Baru</flux:button>
    </div>

    <flux:table class="mt-6">
        <flux:table.columns>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Jenis</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Tanggal</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($posts as $post)
                <flux:table.row wire:key="post-{{ $post->id }}">
                    <flux:table.cell>{{ $post->title }}</flux:table.cell>
                    <flux:table.cell>{{ $post->type->label() }}</flux:table.cell>
                    <flux:table.cell>
                        {{ $post->status->label() }}
                        @if ($post->status->value === 'rejected' && $post->review_note)
                            <flux:text size="sm" class="text-red-600 dark:text-red-400 block">{{ $post->review_note }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ ($post->published_at ?? $post->created_at)->translatedFormat('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $post)
                                <flux:button :href="route('member.posts.edit', $post)" size="sm" wire:navigate>Ubah</flux:button>
                            @endcan
                            @if ($post->status->value === 'published')
                                <flux:button :href="route('posts.show', $post->slug)" size="sm" variant="ghost" wire:navigate>Lihat</flux:button>
                            @endif
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

    <div class="mt-4">{{ $posts->links() }}</div>
</div>
