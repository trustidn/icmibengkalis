<div>
    <x-public.page-header eyebrow="Aktualita" title="Berita & Artikel" subtitle="Informasi kegiatan, publikasi, dan opini terbaru dari ICMI Kabupaten Bengkalis." />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="flex flex-wrap gap-3 mb-10">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari judul..."
                   class="flex-1 min-w-[200px] max-w-xs bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />

            <select wire:model.live="type"
                    class="bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Semua Jenis</option>
                @foreach ($types as $jenis)
                    <option value="{{ $jenis->value }}">{{ $jenis->label() }}</option>
                @endforeach
            </select>

            <select wire:model.live="tag"
                    class="bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Semua Tag</option>
                @foreach ($tags as $t)
                    <option value="{{ $t->slug }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        @if ($posts->isNotEmpty())
            <div class="grid gap-gutter sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    {{-- Kartu memakai overlay-link (bukan <a> pembungkus) agar chip penulis
                         di dalamnya bisa jadi tautan tersendiri tanpa nested anchor. --}}
                    <div wire:key="post-{{ $post->id }}"
                         class="group relative bg-white rounded-xl border border-outline-variant/30 card-shadow-hover transition-all duration-300 overflow-hidden flex flex-col">
                        <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="absolute inset-0 z-0" aria-label="{{ $post->title }}"></a>
                        @if ($post->featuredThumbUrl())
                            <img src="{{ $post->featuredThumbUrl() }}" alt="{{ $post->title }}" class="aspect-video w-full object-cover object-top" />
                        @else
                            <x-public.image-placeholder icon="article" />
                        @endif
                        <div class="p-6 flex flex-col flex-1">
                            <span class="text-secondary font-bold text-[12px] uppercase tracking-widest mb-3">{{ $post->category?->name ?? $post->type->label() }}</span>
                            <h3 class="capitalize font-headline-md text-[20px] leading-tight text-on-surface group-hover:text-primary transition-colors mb-3">{{ $post->title }}</h3>
                            <p class="font-body-md text-on-surface-variant line-clamp-3 opacity-80 flex-1">{{ $post->excerpt }}</p>
                            <div class="mt-4 pt-4 border-t border-outline-variant/20 flex items-center justify-between gap-3">
                                <x-public.author-chip :user="$post->author" class="relative z-10 hover:opacity-80 transition-opacity" />
                                <span class="shrink-0 flex items-center gap-3 text-label-lg font-label-lg text-on-surface-variant/70">
                                    <span class="flex items-center gap-1 text-primary"><span class="material-symbols-outlined text-[15px] [font-variation-settings:'FILL'_1]">thumb_up</span>{{ $post->likes_count }}</span>
                                    {{ $post->published_at?->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="font-body-md text-on-surface-variant text-center py-16">Belum ada berita atau artikel.</p>
        @endif

        <div class="mt-12">{{ $posts->links() }}</div>
    </div>
</div>
