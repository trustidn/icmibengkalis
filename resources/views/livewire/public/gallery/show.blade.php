<div>
    <x-public.page-header :title="$album->title" :subtitle="$album->description" />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        @if ($album->items->isNotEmpty())
            <div class="grid grid-cols-2 gap-5 sm:grid-cols-3">
                @foreach ($album->items as $item)
                    <div wire:key="item-{{ $item->id }}" class="rounded-xl overflow-hidden border border-outline-variant/30 card-shadow-hover transition-all duration-300 bg-white">
                        @if ($item->isVideo())
                            <flux:modal.trigger name="lightbox-{{ $item->id }}">
                                <button type="button" class="relative block w-full aspect-video group">
                                    @if ($item->thumbnailUrl())
                                        <img src="{{ $item->thumbnailUrl() }}" loading="lazy" class="h-full w-full object-cover" alt="{{ $item->caption ?: $album->title }}">
                                    @else
                                        <x-public.image-placeholder icon="videocam" class="aspect-video w-full" />
                                    @endif
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/30 transition-colors">
                                        <span class="material-symbols-outlined text-white text-[48px] drop-shadow-lg">play_circle</span>
                                    </div>
                                </button>
                            </flux:modal.trigger>
                            <flux:modal name="lightbox-{{ $item->id }}" class="max-w-3xl">
                                <div class="aspect-video bg-black rounded-lg overflow-hidden">
                                    @if ($item->embedUrl())
                                        <iframe class="h-full w-full" src="{{ $item->embedUrl() }}"
                                                title="{{ $item->caption ?: $album->title }}"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen loading="lazy"></iframe>
                                    @endif
                                </div>
                                @if ($item->caption)
                                    <p class="mt-3 font-body-md text-on-surface-variant text-sm">{{ $item->caption }}</p>
                                @endif
                            </flux:modal>
                        @elseif ($item->thumbnailUrl())
                            <flux:modal.trigger name="lightbox-{{ $item->id }}">
                                <button type="button" class="block w-full">
                                    <img src="{{ $item->thumbnailUrl() }}" loading="lazy" class="aspect-square w-full object-cover" alt="{{ $item->caption ?: $album->title }}">
                                </button>
                            </flux:modal.trigger>
                            <flux:modal name="lightbox-{{ $item->id }}" class="max-w-3xl">
                                <img src="{{ $item->largeUrl() }}" loading="lazy" class="w-full h-auto rounded-lg" alt="{{ $item->caption ?: $album->title }}">
                                @if ($item->caption)
                                    <p class="mt-3 font-body-md text-on-surface-variant text-sm">{{ $item->caption }}</p>
                                @endif
                            </flux:modal>
                        @else
                            <x-public.image-placeholder icon="photo" class="aspect-square w-full" />
                        @endif

                        @if ($item->caption)
                            <p class="p-3 font-body-md text-on-surface-variant text-sm">{{ $item->caption }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="font-body-md text-on-surface-variant text-center py-16">Belum ada media pada album ini.</p>
        @endif

        <div class="mt-10">
            <a href="{{ route('gallery.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all w-fit">
                <span class="material-symbols-outlined text-[18px]">west</span> Kembali ke Galeri
            </a>
        </div>
    </div>
</div>
