<div>
    {{-- Hero. Di bawah breakpoint lg gambar hero jadi background full-bleed dengan scrim gelap;
         semua kelas teks-putih HARUS kondisional pada $heroUrl — tanpa gambar, teks kembali gelap. --}}
    @php $heroUrl = \App\Models\SiteSetting::current()->heroUrl(); @endphp
    <header class="relative pt-20 {{ $heroUrl ? 'pb-32' : 'pb-24' }} md:pt-32 md:pb-40 px-margin-mobile md:px-margin-desktop overflow-hidden bg-gradient-to-b from-white to-surface-container-low">
        @if ($heroUrl)
            <div class="absolute inset-0 lg:hidden">
                <img src="{{ $heroUrl }}" alt="" class="absolute inset-0 w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-b from-on-surface/70 via-on-surface/50 to-on-surface/60"></div>
                {{-- Lelehkan tepi bawah hero ke warna background halaman; tinggi fade < padding bawah agar tombol tidak masuk zona pudar --}}
                <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-background via-background/60 to-transparent"></div>
            </div>
        @endif
        <div class="absolute inset-0 hero-pattern -z-10 {{ $heroUrl ? 'hidden lg:block' : '' }}"></div>
        <div class="absolute -top-48 -right-48 w-[600px] h-[600px] bg-primary-container/15 rounded-full blur-[100px] {{ $heroUrl ? 'hidden lg:block' : '' }}"></div>
        <div class="absolute top-1/2 -left-48 w-96 h-96 bg-secondary-container/15 rounded-full blur-[80px] {{ $heroUrl ? 'hidden lg:block' : '' }}"></div>

        <div class="relative max-w-container-max mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            <div class="space-y-10">
                <div class="inline-flex items-center gap-3 bg-white/80 backdrop-blur-sm px-5 py-2 rounded-full border border-primary-container/30 shadow-sm">
                    <span class="material-symbols-outlined text-primary text-[20px]">verified</span>
                    <span class="text-primary font-label-lg text-label-lg uppercase tracking-widest font-bold">Inspiratif &amp; Transformatif</span>
                </div>

                <h1 class="font-display-lg-mobile text-display-lg-mobile md:font-display-lg md:text-display-lg {{ $heroUrl ? 'text-white lg:text-on-surface' : 'text-on-surface' }} leading-[1.1] text-glow">
                    Membangun Peradaban Melalui <span class="{{ $heroUrl ? 'text-primary-container lg:text-primary' : 'text-primary' }} italic font-bold">Intelektualitas</span>
                </h1>

                <p class="font-body-lg text-body-lg {{ $heroUrl ? 'text-white/90 lg:text-on-surface-variant' : 'text-on-surface-variant' }} max-w-xl leading-relaxed opacity-90">
                    Portal digital resmi ICMI Kabupaten Bengkalis — mewujudkan masyarakat yang beradab dan berkeadilan melalui peran aktif cendekiawan muslim dalam merespon tantangan zaman.
                </p>

                <div class="flex flex-wrap gap-5 pt-4">
                    <a href="/visi-misi" wire:navigate
                       class="bg-primary text-on-primary px-10 py-4 rounded-full font-label-lg text-label-lg hover:shadow-xl hover:shadow-primary/20 hover:-translate-y-1 transition-all duration-300 flex items-center gap-3">
                        Visi &amp; Misi
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </a>
                    <a href="{{ route('contact.show') }}" wire:navigate
                       class="{{ $heroUrl ? 'bg-white/10 backdrop-blur-md border-2 border-white/40 text-white hover:bg-white/20 lg:bg-white lg:border-primary-container/50 lg:text-primary lg:hover:bg-primary-container/5' : 'bg-white border-2 border-primary-container/50 text-primary hover:bg-primary-container/5' }} px-10 py-4 rounded-full font-label-lg text-label-lg transition-all duration-300">
                        Hubungi Kami
                    </a>
                </div>
            </div>

            <div class="relative h-[280px] sm:h-[360px] lg:h-[480px] {{ $heroUrl ? 'hidden lg:block' : '' }}">
                <div class="absolute inset-0 rounded-full border-[1.5px] border-dashed border-primary/20 animate-[spin_60s_linear_infinite]"></div>
                <div class="absolute inset-8 lg:inset-12 rounded-full border border-primary/10 animate-[spin_40s_linear_infinite_reverse]"></div>
                <div class="absolute inset-0 flex items-center justify-center p-6 lg:p-8">
                    <div class="w-full h-full rounded-xl overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.15)] relative bg-gradient-to-br from-primary to-primary-container flex items-center justify-center">
                        @if ($heroUrl)
                            <img src="{{ $heroUrl }}" alt="{{ \App\Models\SiteSetting::current()->site_name }}" class="absolute inset-0 w-full h-full object-cover" />
                        @else
                            <x-app-logo-icon class="h-20 sm:h-28 lg:h-40 w-auto text-white/90" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Pengumuman terpaku --}}
    @if ($pinnedAnnouncements->isNotEmpty())
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-6 space-y-3">
            @foreach ($pinnedAnnouncements as $announcement)
                <div wire:key="announcement-{{ $announcement->id }}"
                     class="flex items-start gap-4 bg-primary-container/20 border border-primary-container/40 rounded-lg px-6 py-4">
                    <span class="material-symbols-outlined text-primary shrink-0">campaign</span>
                    <p class="font-body-md text-on-surface-variant"><strong class="text-on-surface">{{ $announcement->title }}</strong> — {{ $announcement->body }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Poster ucapan (hari jadi, hari kemerdekaan, dll.) — dikelola dari admin.
         Section disembunyikan sepenuhnya bila tidak ada poster yang sedang tayang. --}}
    @if ($posters->isNotEmpty())
        <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-10 pb-4">
            @if ($posters->count() === 1)
                @php $poster = $posters->first(); @endphp
                @if ($poster->imageUrl())
                    {{-- Desktop: lebar banner dibatasi maks. 40% lebar layar agar tidak mendominasi --}}
                    <div class="rounded-xl overflow-hidden card-shadow border border-outline-variant/20 mx-auto lg:max-w-[40vw]">
                        @if ($poster->link_url)
                            <a href="{{ $poster->link_url }}" target="_blank" rel="noopener">
                                <img src="{{ $poster->imageUrl() }}" alt="{{ $poster->title }}" class="w-full h-auto" />
                            </a>
                        @else
                            <img src="{{ $poster->imageUrl() }}" alt="{{ $poster->title }}" class="w-full h-auto" />
                        @endif
                    </div>
                @endif
            @else
                <div class="flex gap-5 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden [justify-content:safe_center]">
                    @foreach ($posters as $poster)
                        @if ($poster->imageUrl())
                            <div wire:key="poster-{{ $poster->id }}" class="snap-center shrink-0 w-[92%] md:w-[85%] lg:w-[40vw] rounded-xl overflow-hidden card-shadow border border-outline-variant/20">
                                @if ($poster->link_url)
                                    <a href="{{ $poster->link_url }}" target="_blank" rel="noopener">
                                        <img src="{{ $poster->imageUrl() }}" alt="{{ $poster->title }}" class="w-full h-auto" />
                                    </a>
                                @else
                                    <img src="{{ $poster->imageUrl() }}" alt="{{ $poster->title }}" class="w-full h-auto" />
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Pilar --}}
    <section class="section-padding bg-surface overflow-hidden relative border-y border-outline-variant/20">
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <span class="text-primary font-bold tracking-[0.2em] text-label-lg uppercase mb-4 block">Nilai Organisasi</span>
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-6">Pilar Gerakan Kami</h2>
                <div class="h-1 w-24 bg-primary-container mx-auto rounded-full mb-8"></div>
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed px-4 md:px-10">
                    Berdasarkan cita-cita luhur ICMI untuk menjadi wadah bagi pemikiran kritis dan aksi nyata yang berdampak signifikan bagi kemajuan bangsa dan peradaban.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="group bg-white p-10 md:p-12 rounded-xl border border-outline-variant/30 card-shadow card-shadow-hover transition-all duration-500 relative overflow-hidden">
                    <div class="absolute -right-16 -bottom-16 text-[240px] font-headline-lg text-primary/5 pointer-events-none select-none">I</div>
                    <div class="bg-primary-container/10 w-20 h-20 rounded-xl flex items-center justify-center mb-10 group-hover:bg-primary transition-all duration-500">
                        <span class="material-symbols-outlined text-[36px] text-primary group-hover:text-white">auto_awesome</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-6 group-hover:text-primary transition-colors">Inspiratif</h3>
                    <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed opacity-90">
                        Menjadi sumber inspirasi bagi masyarakat melalui pemikiran yang jernih, akhlak yang mulia, dan kontribusi intelektual yang mencerahkan.
                    </p>
                </div>

                <div class="group bg-white p-10 md:p-12 rounded-xl border border-outline-variant/30 card-shadow card-shadow-hover transition-all duration-500 relative overflow-hidden">
                    <div class="absolute -right-16 -bottom-16 text-[240px] font-headline-lg text-secondary/5 pointer-events-none select-none">T</div>
                    <div class="bg-secondary-container/10 w-20 h-20 rounded-xl flex items-center justify-center mb-10 group-hover:bg-secondary transition-all duration-500">
                        <span class="material-symbols-outlined text-[36px] text-secondary group-hover:text-white">rocket_launch</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-6 group-hover:text-secondary transition-colors">Transformatif</h3>
                    <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed opacity-90">
                        Mendorong transformasi sosial yang positif melalui kebijakan berbasis riset, pemberdayaan umat berkelanjutan, dan sinergi antar cendekiawan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Sambutan Ketua.
         Mobile (< lg): kartu foto ketua dengan label jabatan+nama, ditimpa kartu putih
         berisi kutipan yang overlap ke atas foto (negative margin).
         Desktop (lg+): panel gelap full-bleed, foto jadi background dengan scrim horizontal. --}}
    @if ($greetingPage)
        <section class="lg:hidden bg-surface-container-low border-b border-outline-variant/20 px-margin-mobile py-14">
            <div class="max-w-md mx-auto">
                <div class="relative rounded-2xl overflow-hidden shadow-[0_24px_48px_-12px_rgba(0,0,0,0.25)]">
                    @if ($greetingPage->featuredImageUrl())
                        <img src="{{ $greetingPage->featuredImageUrl() }}" alt="{{ $greetingPage->title }}" class="aspect-[3/4] w-full object-cover object-top" />
                    @else
                        <x-public.image-placeholder icon="record_voice_over" class="aspect-[3/4] w-full" />
                    @endif
                    <div class="absolute inset-x-0 bottom-0 h-2/5 bg-gradient-to-t from-on-surface/90 to-transparent"></div>
                    @if ($chairman)
                        <div class="absolute inset-x-0 bottom-0 px-6 pb-24 space-y-1">
                            <p class="text-primary-container font-bold tracking-[0.2em] text-label-lg uppercase">{{ $chairman->position_title }} ICMI</p>
                            <p class="text-white font-headline-md text-[20px] font-bold">{{ $chairman->displayName() }}</p>
                        </div>
                    @endif
                </div>

                <div class="relative -mt-16 mx-3 bg-white rounded-2xl shadow-xl px-7 py-10 text-center space-y-6">
                    <span class="material-symbols-outlined text-outline-variant/70 text-[56px] leading-none">format_quote</span>
                    <blockquote class="font-headline-md text-[20px] italic text-on-surface leading-relaxed">
                        &ldquo;{{ $greetingExcerpt }}&rdquo;
                    </blockquote>
                    <div class="w-12 h-[3px] bg-secondary mx-auto rounded-full"></div>
                    @php $tagline = \App\Models\SiteSetting::current()->tagline; @endphp
                    @if ($tagline)
                        <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">{{ $tagline }}</p>
                    @endif
                    <div class="space-y-3 pt-2">
                        <a href="/sambutan-ketua" wire:navigate
                           class="flex items-center justify-center gap-2 w-full bg-primary-container text-on-primary-container px-6 py-4 rounded-lg font-label-lg text-label-lg font-bold uppercase tracking-wider hover:shadow-lg transition-all duration-300">
                            Baca Selengkapnya
                            <span class="material-symbols-outlined text-[20px]">east</span>
                        </a>
                        <a href="/tentang" wire:navigate
                           class="flex items-center justify-center gap-2 w-full border border-outline-variant/50 text-on-surface-variant px-6 py-4 rounded-lg font-label-lg text-label-lg font-bold uppercase tracking-wider hover:border-primary hover:text-primary transition-all duration-300">
                            <span class="material-symbols-outlined text-[20px]">description</span>
                            Profil ICMI
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden bg-on-surface border-b border-outline-variant/20 hidden lg:block">
            @if ($greetingPage->featuredImageUrl())
                <img src="{{ $greetingPage->featuredImageUrl() }}" alt="{{ $greetingPage->title }}"
                     class="absolute inset-0 w-full h-full object-cover object-right-top" />
                <div class="absolute inset-0 bg-gradient-to-r from-on-surface/95 via-on-surface/70 to-on-surface/15"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-on-surface via-on-surface to-primary/30"></div>
            @endif

            <div class="relative max-w-container-max mx-auto px-margin-desktop py-16 xl:py-20">
                <div class="max-w-2xl space-y-6">
                    <div class="flex items-center gap-4">
                        <span class="h-[2px] w-10 bg-primary-container shrink-0"></span>
                        <span class="text-primary-container font-bold tracking-[0.25em] text-label-lg uppercase">{{ $greetingPage->title }}</span>
                    </div>

                    <blockquote class="text-white font-headline-lg italic font-bold leading-snug text-[24px] xl:text-[26px]">
                        &ldquo;{{ $greetingExcerpt }}&rdquo;
                    </blockquote>

                    @if ($chairman)
                        <div class="border-l-4 border-primary-container pl-5 space-y-1">
                            <p class="text-white font-headline-md text-[17px] font-bold">{{ $chairman->displayName() }}</p>
                            <p class="text-white/60 font-label-lg text-label-lg uppercase tracking-widest">
                                {{ $chairman->position_title }} ICMI Bengkalis {{ $chairman->unit->period->name }}
                            </p>
                        </div>
                    @endif

                    <div class="pt-1">
                        <a href="/sambutan-ketua" wire:navigate
                           class="inline-flex items-center gap-3 bg-primary text-on-primary px-7 py-3.5 rounded-full font-label-lg text-label-lg font-bold uppercase tracking-wider hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-1 transition-all duration-300">
                            Baca Selengkapnya
                            <span class="material-symbols-outlined text-[20px]">east</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Cendekiawan Kami --}}
    @if ($featuredMembers->isNotEmpty())
        <section class="section-padding px-margin-mobile md:px-margin-desktop bg-surface overflow-hidden" x-data="{ track: null }">
            <div class="max-w-container-max mx-auto">
                <div class="flex flex-wrap items-end justify-between gap-6 mb-12">
                    <div>
                        <span class="text-primary font-bold tracking-[0.2em] text-label-lg uppercase mb-4 block">Anggota</span>
                        <h2 class="font-headline-lg text-headline-lg text-on-surface">Cendekiawan Kami</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" x-on:click="$refs.track.scrollBy({ left: -$refs.track.clientWidth * 0.8, behavior: 'smooth' })"
                                class="w-11 h-11 rounded-full bg-white border border-outline-variant/40 text-on-surface-variant hover:border-primary hover:text-primary transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button type="button" x-on:click="$refs.track.scrollBy({ left: $refs.track.clientWidth * 0.8, behavior: 'smooth' })"
                                class="w-11 h-11 rounded-full bg-white border border-outline-variant/40 text-on-surface-variant hover:border-primary hover:text-primary transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                </div>

                {{-- [justify-content:safe_center]: rata tengah saat semua kartu muat, otomatis kembali
                     rata kiri saat overflow (scroll) — browser lama mengabaikannya dengan aman.
                     Lebar kartu membagi rata track (dikurangi total gap 1rem×n) agar baris penuh seimbang. --}}
                <div x-ref="track" class="flex gap-4 [justify-content:safe_center] overflow-x-auto snap-x snap-mandatory pb-4 -mx-margin-mobile px-margin-mobile md:mx-0 md:px-0 scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($featuredMembers as $member)
                        <x-public.member-link :member="$member"
                            class="snap-start shrink-0 w-[38%] sm:w-[calc((100%-2rem)/3)] md:w-[calc((100%-3rem)/4)] lg:w-[calc((100%-4rem)/5)] block bg-white rounded-xl border border-outline-variant/30 overflow-hidden card-shadow-hover transition-all duration-300">
                            <x-public.member-photo :member="$member" class="aspect-[3/4] w-full" />
                            <div class="p-3 text-center">
                                <p class="font-headline-md text-[13px] text-on-surface leading-tight line-clamp-2">{{ $member->full_name }}</p>
                                @if ($member->profession)
                                    <p class="font-body-md text-on-surface-variant text-xs mt-1 truncate">{{ $member->profession }}</p>
                                @endif
                            </div>
                        </x-public.member-link>
                    @endforeach
                </div>

                <div class="flex justify-end mt-6">
                    <a href="{{ route('org-chart.show') }}" wire:navigate
                       class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all">
                        {{ __('nav.org_structure') }}
                        <span class="material-symbols-outlined text-[18px]">trending_flat</span>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- Warta --}}
    <section class="section-padding px-margin-mobile md:px-margin-desktop bg-white">
        <div class="max-w-container-max mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-16">
                <div class="max-w-2xl">
                    <span class="text-primary font-bold tracking-[0.2em] text-label-lg uppercase mb-4 block">Aktualita</span>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-4">Warta Intelektual</h2>
                    <p class="text-on-surface-variant font-body-md opacity-80">Informasi kegiatan, publikasi, dan agenda terbaru dari ICMI Kabupaten Bengkalis.</p>
                </div>
                <a href="{{ route('posts.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-4 transition-all duration-300 shrink-0">
                    Semua Berita
                    <span class="material-symbols-outlined">east</span>
                </a>
            </div>

            @if (count($latestPosts) > 0)
                <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-gutter">
                    {{-- Berita utama --}}
                    <article class="md:col-span-2 md:row-span-2 group relative rounded-xl overflow-hidden shadow-2xl min-h-[420px] bg-gradient-to-br from-primary to-on-primary-fixed-variant flex items-center justify-center">
                        @if ($latestPosts[0]->featuredImageUrl())
                            <img src="{{ $latestPosts[0]->featuredImageUrl() }}" alt="{{ $latestPosts[0]->title }}" class="absolute inset-0 w-full h-full object-cover" />
                        @else
                            <span class="material-symbols-outlined text-white/20 text-[160px]">article</span>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-on-surface/90 via-on-surface/40 to-transparent"></div>
                        <a href="{{ route('posts.show', $latestPosts[0]->slug) }}" wire:navigate class="absolute inset-0 z-10" aria-label="{{ $latestPosts[0]->title }}"></a>
                        <div class="absolute bottom-0 left-0 p-8 md:p-10 space-y-5 w-full">
                            <span class="bg-primary-container text-on-primary-container px-4 py-1.5 rounded text-label-lg font-bold shadow-lg">
                                {{ $latestPosts[0]->category?->name ?? $latestPosts[0]->type->label() }}
                            </span>
                            <h3 class="text-white font-headline-lg text-headline-lg leading-tight group-hover:text-primary-container transition-colors duration-300">
                                {{ $latestPosts[0]->title }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-6 text-white/80 font-label-lg text-label-lg pt-4 border-t border-white/20">
                                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">calendar_today</span> {{ $latestPosts[0]->published_at?->translatedFormat('d F Y') }}</span>
                                @if ($latestPosts[0]->author)
                                    <x-public.member-link :member="$latestPosts[0]->author->member" class="relative z-20 flex items-center gap-2 hover:text-primary-container transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">account_circle</span> {{ $latestPosts[0]->author->name }}
                                    </x-public.member-link>
                                @endif
                            </div>
                        </div>
                    </article>

                    @foreach ($latestPosts->slice(1, 3) as $post)
                        <article wire:key="post-{{ $post->id }}" class="md:col-span-2 bg-white rounded-xl border border-outline-variant/30 group card-shadow-hover transition-all duration-300 flex gap-6 p-6 relative">
                            <a href="{{ route('posts.show', $post->slug) }}" wire:navigate class="absolute inset-0" aria-label="{{ $post->title }}"></a>
                            @if ($post->featuredImageUrl())
                                <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="w-28 h-28 shrink-0 rounded-lg object-cover" />
                            @else
                                <x-public.image-placeholder icon="article" class="w-28 h-28 shrink-0 aspect-auto" />
                            @endif
                            <div class="flex flex-col justify-center min-w-0">
                                <span class="text-secondary font-bold text-[12px] uppercase tracking-widest mb-2">{{ $post->category?->name ?? $post->type->label() }}</span>
                                <h4 class="font-headline-md text-[18px] group-hover:text-primary transition-colors leading-tight mb-2">{{ $post->title }}</h4>
                                <p class="text-on-surface-variant font-body-md line-clamp-2 opacity-80 text-sm">{{ $post->excerpt }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="font-body-md text-on-surface-variant">Belum ada berita.</p>
            @endif
        </div>
    </section>

    {{-- Agenda & Galeri --}}
    <section class="section-padding px-margin-mobile md:px-margin-desktop bg-surface-container-low">
        <div class="max-w-container-max mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16">
            <div>
                <div class="flex items-center justify-between mb-10">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface">Agenda Mendatang</h2>
                    <a href="{{ route('agenda.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all">
                        Lihat semua <span class="material-symbols-outlined text-[18px]">trending_flat</span>
                    </a>
                </div>
                <div class="space-y-5">
                    @forelse ($upcomingEvents as $event)
                        <a wire:key="event-{{ $event->id }}" href="{{ route('agenda.show', $event->slug) }}" wire:navigate
                           class="group flex items-center gap-6 bg-white p-6 rounded-xl border border-outline-variant/30 card-shadow-hover transition-all duration-300">
                            <div class="shrink-0 w-16 h-16 rounded-lg bg-primary-container/15 flex flex-col items-center justify-center text-primary">
                                <span class="font-bold text-lg leading-none">{{ $event->starts_at->format('d') }}</span>
                                <span class="text-[11px] uppercase font-bold">{{ $event->starts_at->translatedFormat('M') }}</span>
                            </div>
                            <h4 class="font-headline-md text-[18px] text-on-surface group-hover:text-primary transition-colors leading-tight">{{ $event->title }}</h4>
                        </a>
                    @empty
                        <p class="font-body-md text-on-surface-variant">Belum ada agenda mendatang.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-10">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface">Galeri Terbaru</h2>
                    <a href="{{ route('gallery.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all">
                        Lihat semua <span class="material-symbols-outlined text-[18px]">trending_flat</span>
                    </a>
                </div>
                <div class="grid grid-cols-3 gap-3 sm:gap-5">
                    @forelse ($latestGalleryItems as $item)
                        @php $thumb = $item->thumbnailUrl(); @endphp
                        @php $tileClasses = "group relative aspect-square rounded-xl overflow-hidden flex items-end p-3 text-center card-shadow-hover transition-all duration-300 w-full " . ($thumb ? '' : 'bg-gradient-to-br from-tertiary to-tertiary-container'); @endphp

                        <div wire:key="gallery-item-{{ $item->id }}">
                            @if ($item->isVideo())
                                <flux:modal.trigger name="home-lightbox-{{ $item->id }}">
                                    <button type="button" class="{{ $tileClasses }}">
                                        @if ($thumb)
                                            <img src="{{ $thumb }}" alt="{{ $item->caption ?: $item->album->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                            <div class="absolute inset-0 bg-gradient-to-t from-on-surface/80 to-transparent"></div>
                                        @else
                                            <span class="material-symbols-outlined text-white/70 text-[28px] absolute top-5 left-1/2 -translate-x-1/2">photo_library</span>
                                        @endif
                                        <span class="material-symbols-outlined text-white text-[32px] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 drop-shadow-lg">play_circle</span>
                                        <span class="relative z-10 text-white font-label-lg text-[13px] leading-tight w-full line-clamp-2">{{ $item->album->title }}</span>
                                    </button>
                                </flux:modal.trigger>
                                <flux:modal name="home-lightbox-{{ $item->id }}" class="max-w-3xl">
                                    <div class="aspect-video bg-black rounded-lg overflow-hidden">
                                        @if ($item->embedUrl())
                                            <iframe class="h-full w-full" src="{{ $item->embedUrl() }}"
                                                    title="{{ $item->caption ?: $item->album->title }}"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen loading="lazy"></iframe>
                                        @endif
                                    </div>
                                    <p class="mt-3 font-body-md text-on-surface-variant text-sm">{{ $item->caption ?: $item->album->title }}</p>
                                </flux:modal>
                            @else
                                <a href="{{ route('gallery.show', $item->album->slug) }}" wire:navigate class="{{ $tileClasses }}">
                                    @if ($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $item->caption ?: $item->album->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                        <div class="absolute inset-0 bg-gradient-to-t from-on-surface/80 to-transparent"></div>
                                    @else
                                        <span class="material-symbols-outlined text-white/70 text-[28px] absolute top-5 left-1/2 -translate-x-1/2">photo_library</span>
                                    @endif
                                    <span class="relative z-10 text-white font-label-lg text-[13px] leading-tight w-full line-clamp-2">{{ $item->album->title }}</span>
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="font-body-md text-on-surface-variant col-span-3">Belum ada foto atau video galeri.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="section-padding px-margin-mobile md:px-margin-desktop">
        <div class="max-w-container-max mx-auto bg-on-surface rounded-xl p-12 md:p-24 relative overflow-hidden shadow-[0_40px_80px_-15px_rgba(0,0,0,0.3)]">
            <div class="absolute -right-20 -top-20 w-96 h-96 bg-primary-container/10 rounded-full blur-[100px]"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-primary/10 rounded-full blur-[80px]"></div>
            <div class="relative z-10 flex flex-col lg:flex-row items-center gap-16">
                <div class="flex-1 text-center lg:text-left">
                    <h2 class="text-white font-headline-lg text-headline-lg lg:font-display-lg lg:text-display-lg mb-8 leading-tight">
                        Mari Berkontribusi Bagi <span class="text-primary-container">Peradaban Bangsa</span>
                    </h2>
                    <p class="text-white/70 font-body-lg text-body-lg max-w-xl leading-relaxed">
                        Bergabunglah dengan jaringan cendekiawan muslim di Kabupaten Bengkalis untuk merumuskan masa depan yang lebih beradab.
                    </p>
                </div>
                {{-- Keanggotaan tertutup: tautan pendaftaran tidak ditampilkan untuk publik. --}}
                <div class="w-full lg:w-auto flex flex-col sm:flex-row gap-5">
                    <a href="{{ route('contact.show') }}" wire:navigate
                       class="bg-primary-container text-on-primary-container px-10 py-4 rounded-full font-bold text-label-lg hover:bg-white hover:text-primary transition-all duration-300 shadow-xl text-center">
                        Hubungi Kami
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
