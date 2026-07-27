@props(['icon' => 'image', 'class' => 'aspect-[16/10] w-full'])

<div {{ $attributes->merge(['class' => "$class rounded-xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center"]) }}>
    <span class="material-symbols-outlined text-white/70 text-[40px]">{{ $icon }}</span>
</div>
