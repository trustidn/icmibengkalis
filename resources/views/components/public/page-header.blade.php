@props(['eyebrow' => null, 'title', 'subtitle' => null])

<header class="relative px-margin-mobile md:px-margin-desktop pt-16 pb-12 md:pt-24 md:pb-16 overflow-hidden bg-gradient-to-b from-white to-surface-container-low border-b border-outline-variant/20">
    <div class="absolute inset-0 hero-pattern -z-10"></div>
    <div class="absolute -top-32 -right-32 w-80 h-80 bg-primary-container/15 rounded-full blur-[90px] -z-10"></div>

    <div class="max-w-container-max mx-auto">
        @if ($eyebrow)
            <span class="text-primary font-bold tracking-[0.2em] text-label-lg uppercase mb-4 block">{{ $eyebrow }}</span>
        @endif
        <h1 class="font-headline-lg text-headline-lg md:font-display-lg md:text-display-lg text-on-surface">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-4 font-body-lg text-body-lg text-on-surface-variant max-w-2xl">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</header>
