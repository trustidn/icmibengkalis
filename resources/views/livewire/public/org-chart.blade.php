<div x-data="{ zoom: 1 }">
    <x-public.page-header eyebrow="Organisasi" title="Struktur Organisasi" subtitle="Susunan pengurus ICMI Kabupaten Bengkalis per periode." />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="flex flex-wrap items-center gap-3 mb-8">
            <select wire:model.live="periodId"
                    class="bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                @foreach ($periods as $period)
                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                @endforeach
            </select>

            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari pengurus..."
                   class="bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface placeholder:text-outline max-w-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />

            <div class="ml-auto flex items-center gap-2">
                <button type="button" x-on:click="zoom = Math.max(0.5, zoom - 0.1)"
                        class="w-10 h-10 rounded-full bg-white border border-outline-variant/40 text-on-surface-variant hover:border-primary hover:text-primary transition-colors">−</button>
                <button type="button" x-on:click="zoom = 1"
                        class="px-4 h-10 rounded-full bg-white border border-outline-variant/40 text-on-surface-variant font-label-lg text-label-lg hover:border-primary hover:text-primary transition-colors">Reset</button>
                <button type="button" x-on:click="zoom = Math.min(1.5, zoom + 0.1)"
                        class="w-10 h-10 rounded-full bg-white border border-outline-variant/40 text-on-surface-variant hover:border-primary hover:text-primary transition-colors">+</button>
            </div>
        </div>

        <div class="overflow-auto rounded-xl border border-outline-variant/30 bg-surface-container-low/50 p-6 md:p-10 card-shadow">
            <div :style="`transform: scale(${zoom}); transform-origin: top left;`" class="w-max min-w-full">
                @if ($units->isNotEmpty())
                    <ul class="org-tree">
                        @foreach ($units as $unit)
                            <x-org-chart.node :unit="$unit" :search="$search" />
                        @endforeach
                    </ul>
                @else
                    <p class="font-body-md text-on-surface-variant">Belum ada struktur organisasi untuk periode ini.</p>
                @endif
            </div>
        </div>

        <div class="mt-16">
            <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Daftar Anggota</h2>
            <p class="font-body-md text-on-surface-variant mb-8">Seluruh anggota ICMI Kabupaten Bengkalis, diurutkan berdasarkan jabatan, tanggal lahir, lalu nama.</p>

            <div class="grid grid-cols-3 gap-4 md:gap-6 lg:grid-cols-5">
                @forelse ($members as $member)
                    <x-public.member-link :member="$member" class="block bg-white rounded-xl border border-outline-variant/30 overflow-hidden card-shadow-hover transition-all duration-300">
                        <x-public.member-photo :member="$member" class="aspect-[3/4] w-full" />
                        <div class="p-3 text-center">
                            <p class="font-headline-md text-[15px] text-on-surface leading-tight line-clamp-2">{{ $member->full_name }}</p>
                            @if ($member->profession)
                                <p class="font-body-md text-on-surface-variant text-sm mt-1 truncate">{{ $member->profession }}</p>
                            @endif
                        </div>
                    </x-public.member-link>
                @empty
                    <p class="col-span-full p-6 font-body-md text-on-surface-variant">Belum ada data anggota.</p>
                @endforelse
            </div>

            @if ($hasMore)
                <div class="mt-8 flex justify-center">
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled"
                            class="flex items-center gap-2 rounded-full border border-primary-container/60 bg-white px-8 py-3 font-label-lg text-label-lg font-bold text-primary transition-all hover:bg-primary-container/10 disabled:opacity-60">
                        <span wire:loading.remove wire:target="loadMore" class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">expand_more</span> Muat Lebih Banyak
                        </span>
                        <span wire:loading wire:target="loadMore">Memuat…</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
