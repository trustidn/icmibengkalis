<div>
    <x-public.page-header eyebrow="Dokumentasi" title="Galeri" subtitle="Momen kegiatan dan dokumentasi ICMI Kabupaten Bengkalis." />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        @if ($albums->isNotEmpty())
            <div class="grid gap-gutter grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($albums as $album)
                    @php $thumb = $album->items->first()?->thumbnailUrl(); @endphp
                    <a wire:key="album-{{ $album->id }}" href="{{ route('gallery.show', $album->slug) }}" wire:navigate
                       class="group bg-white rounded-xl border border-outline-variant/30 card-shadow-hover transition-all duration-300 overflow-hidden flex flex-col">
                        <div class="relative aspect-[16/10] w-full overflow-hidden bg-surface-container-low">
                            @if ($thumb)
                                <img src="{{ $thumb }}" alt="{{ $album->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            @else
                                <x-public.image-placeholder icon="photo_library" class="w-full h-full" />
                            @endif

                            @if ($album->type === 'video')
                                <div class="absolute inset-0 flex items-center justify-center bg-black/10 group-hover:bg-black/20 transition-colors">
                                    <span class="material-symbols-outlined text-white text-[48px] drop-shadow-lg">play_circle</span>
                                </div>
                            @endif

                            <span class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm text-on-surface-variant px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">{{ $album->type === 'video' ? 'videocam' : 'photo_camera' }}</span>
                                {{ $album->items->count() }}
                            </span>
                        </div>
                        <div class="p-6 flex flex-col flex-1">
                            <span class="text-secondary font-bold text-[12px] uppercase tracking-widest mb-3">{{ ucfirst($album->type) }}</span>
                            <h3 class="font-headline-md text-[20px] leading-tight text-on-surface group-hover:text-primary transition-colors mb-3">{{ $album->title }}</h3>
                            <p class="font-body-md text-on-surface-variant line-clamp-2 opacity-80 flex-1">{{ $album->description }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="font-body-md text-on-surface-variant text-center py-16">Belum ada album galeri.</p>
        @endif

        <div class="mt-12">{{ $albums->links() }}</div>
    </div>
</div>
