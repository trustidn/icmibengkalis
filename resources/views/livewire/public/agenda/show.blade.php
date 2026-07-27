<div>
    <div class="relative bg-gradient-to-b from-white to-surface-container-low border-b border-outline-variant/20 px-margin-mobile md:px-margin-desktop pt-16 pb-12 md:pt-24 md:pb-16 overflow-hidden">
        <div class="absolute inset-0 hero-pattern -z-10"></div>
        <div class="max-w-3xl mx-auto">
            <span class="text-secondary font-bold text-[12px] uppercase tracking-widest">Agenda</span>
            <h1 class="mt-3 font-headline-lg text-headline-lg md:font-display-lg md:text-display-lg text-on-surface leading-tight">{{ $event->title }}</h1>
            <div class="mt-6 flex flex-wrap items-center gap-6 font-label-lg text-label-lg text-on-surface-variant">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                    {{ $event->starts_at->translatedFormat('d F Y, H:i') }}
                    @if ($event->ends_at) – {{ $event->ends_at->translatedFormat('H:i') }} @endif
                </span>
                @if ($event->location)
                    <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">location_on</span> {{ $event->location }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed [&_a]:text-primary [&_a]:underline">
            {!! nl2br(e($event->description)) !!}
        </div>

        <div class="mt-10">
            <a href="{{ route('agenda.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all w-fit">
                <span class="material-symbols-outlined text-[18px]">west</span> Kembali ke Agenda
            </a>
        </div>
    </div>
</div>
