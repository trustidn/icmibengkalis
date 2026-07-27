<div>
    <x-public.page-header eyebrow="Kalender" title="Agenda" subtitle="Kegiatan dan acara mendatang ICMI Kabupaten Bengkalis." />

    <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="flex flex-col gap-5">
            @forelse ($events as $event)
                <a wire:key="event-{{ $event->id }}" href="{{ route('agenda.show', $event->slug) }}" wire:navigate
                   class="group flex items-center gap-6 bg-white p-6 rounded-xl border border-outline-variant/30 card-shadow-hover transition-all duration-300">
                    <div class="shrink-0 w-16 h-16 rounded-lg bg-primary-container/15 flex flex-col items-center justify-center text-primary">
                        <span class="font-bold text-lg leading-none">{{ $event->starts_at->format('d') }}</span>
                        <span class="text-[11px] uppercase font-bold">{{ $event->starts_at->translatedFormat('M') }}</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-headline-md text-[18px] text-on-surface group-hover:text-primary transition-colors leading-tight">{{ $event->title }}</h3>
                        <p class="mt-1 font-label-lg text-label-lg text-on-surface-variant">{{ $event->starts_at->translatedFormat('d F Y, H:i') }}</p>
                        @if ($event->location)
                            <p class="mt-1 flex items-center gap-1 font-label-lg text-label-lg text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px]">location_on</span> {{ $event->location }}
                            </p>
                        @endif
                    </div>
                    <span class="material-symbols-outlined text-outline group-hover:text-primary group-hover:translate-x-1 transition-all">chevron_right</span>
                </a>
            @empty
                <p class="font-body-md text-on-surface-variant text-center py-16">Belum ada agenda mendatang.</p>
            @endforelse
        </div>

        <div class="mt-12">{{ $events->links() }}</div>
    </div>
</div>
