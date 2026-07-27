<div>
    <x-public.page-header eyebrow="Informasi" title="Pengumuman" subtitle="Pengumuman resmi dari pengurus ICMI Kabupaten Bengkalis." />

    <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="flex flex-col gap-5">
            @forelse ($announcements as $announcement)
                <div wire:key="announcement-{{ $announcement->id }}" class="bg-white p-8 rounded-xl border border-outline-variant/30 card-shadow-hover transition-all duration-300">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="font-headline-md text-[20px] text-on-surface">{{ $announcement->title }}</h3>
                        @if ($announcement->is_pinned)
                            <span class="shrink-0 bg-secondary-container text-on-secondary-container px-3 py-1 rounded text-label-lg font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">push_pin</span> Disematkan
                            </span>
                        @endif
                    </div>
                    <p class="mt-3 font-body-md text-on-surface-variant leading-relaxed">{{ $announcement->body }}</p>
                </div>
            @empty
                <p class="font-body-md text-on-surface-variant text-center py-16">Belum ada pengumuman aktif.</p>
            @endforelse
        </div>
    </div>
</div>
