<div>
    <x-public.page-header
        eyebrow="Profil Anggota"
        :title="$member->full_name"
        :subtitle="collect([$member->profession, $member->institution])->filter()->implode(' — ') ?: null"
    />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 grid grid-cols-1 lg:grid-cols-3 gap-10">
        {{-- Kolom kiri: identitas ringkas --}}
        <aside class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl border border-outline-variant/30 p-6 text-center card-shadow-hover">
                @if ($member->photoUrl())
                    <img src="{{ $member->photoUrl() }}" alt="{{ $member->full_name }}"
                         class="w-36 h-36 rounded-full object-cover mx-auto shadow-md" />
                @else
                    <x-public.image-placeholder icon="person" class="w-36 h-36 mx-auto rounded-full" />
                @endif

                <h2 class="mt-5 font-headline-md text-[20px] text-on-surface">
                    {{ trim($member->title_prefix.' '.$member->full_name.' '.$member->title_suffix) }}
                </h2>
                @if ($member->profession)
                    <p class="text-primary font-bold text-sm mt-1">{{ $member->profession }}</p>
                @endif

                <span class="inline-flex items-center gap-1.5 mt-4 bg-primary-container/10 text-primary px-4 py-1.5 rounded-full text-label-lg font-bold">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    Anggota ICMI — {{ $member->nia }}
                </span>

                <div class="mt-5 flex items-center justify-center gap-6">
                    <flux:modal.trigger name="share-profile">
                        <button type="button" class="inline-flex items-center gap-2 text-primary font-bold text-sm hover:gap-3 transition-all">
                            <span class="material-symbols-outlined text-[18px]">share</span>
                            Bagikan Profil
                        </button>
                    </flux:modal.trigger>

                    @if (auth()->user()?->member?->id === $member->id)
                        <a href="{{ route('member.profile.edit') }}" wire:navigate
                           class="inline-flex items-center gap-2 text-primary font-bold text-sm hover:gap-3 transition-all">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            Edit Profil
                        </a>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-outline-variant/30 p-6 card-shadow-hover space-y-4">
                <h3 class="font-headline-md text-[16px] text-on-surface uppercase tracking-wide">Informasi</h3>

                @if ($member->district)
                    <p class="flex items-center gap-3 text-on-surface-variant text-sm">
                        <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                        Kecamatan {{ $member->district->name }}
                    </p>
                @endif

                @if ($member->institution)
                    <p class="flex items-center gap-3 text-on-surface-variant text-sm">
                        <span class="material-symbols-outlined text-primary text-[18px]">apartment</span>
                        {{ $member->institution }}
                    </p>
                @endif

                @if ($member->show_contact_public)
                    @php $links = $member->social_links ?? []; @endphp
                    @if (! empty($links['whatsapp']))
                        <p class="flex items-center gap-3 text-on-surface-variant text-sm">
                            <span class="material-symbols-outlined text-primary text-[18px]">call</span>
                            {{ $links['whatsapp'] }}
                        </p>
                    @endif
                    @if (! empty($links['website']))
                        <p class="flex items-center gap-3 text-on-surface-variant text-sm">
                            <span class="material-symbols-outlined text-primary text-[18px]">language</span>
                            <a href="{{ $links['website'] }}" target="_blank" rel="noopener" class="hover:text-primary underline">{{ $links['website'] }}</a>
                        </p>
                    @endif
                    @if (! empty($links['linkedin']))
                        <p class="flex items-center gap-3 text-on-surface-variant text-sm">
                            <span class="material-symbols-outlined text-primary text-[18px]">work</span>
                            <a href="{{ $links['linkedin'] }}" target="_blank" rel="noopener" class="hover:text-primary underline">LinkedIn</a>
                        </p>
                    @endif
                @endif
            </div>
        </aside>

        {{-- Kolom kanan: CV --}}
        <div class="lg:col-span-2 space-y-8">
            @if ($member->bio)
                <section class="bg-white rounded-xl border border-outline-variant/30 p-6 md:p-8 card-shadow-hover">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">person</span> Tentang
                    </h3>
                    <p class="font-body-md text-on-surface-variant leading-relaxed whitespace-pre-line">{{ $member->bio }}</p>
                </section>
            @endif

            @if ($member->expertise)
                <section class="bg-white rounded-xl border border-outline-variant/30 p-6 md:p-8 card-shadow-hover">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">psychology</span> Bidang Keahlian
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach (preg_split('/,\s*/', $member->expertise, -1, PREG_SPLIT_NO_EMPTY) as $field)
                            <span class="bg-surface-container text-on-surface-variant px-4 py-1.5 rounded-full text-label-lg font-label-lg">{{ $field }}</span>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($member->educations->isNotEmpty())
                <section class="bg-white rounded-xl border border-outline-variant/30 p-6 md:p-8 card-shadow-hover">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-5 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">school</span> Riwayat Pendidikan
                    </h3>
                    <div class="space-y-4">
                        @foreach ($member->educations->sortByDesc('graduated_year') as $education)
                            <div class="flex gap-4 pb-4 border-b border-outline-variant/20 last:border-0 last:pb-0">
                                <span class="shrink-0 bg-primary-container/10 text-primary font-bold px-3 py-1 rounded-lg text-sm h-fit">{{ $education->level->label() }}</span>
                                <div>
                                    <p class="font-bold text-on-surface">{{ $education->institution }}</p>
                                    <p class="text-on-surface-variant text-sm">
                                        {{ $education->major }}
                                        @if ($education->graduated_year) · Lulus {{ $education->graduated_year }} @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($member->orgAssignments->isNotEmpty())
                <section class="bg-white rounded-xl border border-outline-variant/30 p-6 md:p-8 card-shadow-hover">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-5 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">workspace_premium</span> Riwayat Organisasi
                    </h3>
                    <div class="space-y-3">
                        @foreach ($member->orgAssignments as $assignment)
                            <div class="flex items-center justify-between gap-4 pb-3 border-b border-outline-variant/20 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-bold text-on-surface">{{ $assignment->position_title }}</p>
                                    <p class="text-on-surface-variant text-sm">{{ $assignment->unit?->name }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($writings->isNotEmpty())
                <section class="bg-white rounded-xl border border-outline-variant/30 p-6 md:p-8 card-shadow-hover">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-5 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">article</span> Tulisan &amp; Publikasi
                    </h3>
                    <div class="space-y-4">
                        @foreach ($writings as $post)
                            <a href="{{ route('posts.show', $post->slug) }}" wire:navigate
                               class="block pb-4 border-b border-outline-variant/20 last:border-0 last:pb-0 group">
                                <p class="font-bold text-on-surface group-hover:text-primary transition-colors">{{ $post->title }}</p>
                                <p class="text-on-surface-variant text-sm mt-1">{{ $post->published_at?->translatedFormat('d F Y') }}</p>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    <flux:modal name="share-profile" class="max-w-sm">
        <div x-data="shareProfile('{{ $profileUrl }}')" x-init="renderQr()" class="flex flex-col items-center gap-4 text-center">
            <flux:heading size="lg">Bagikan Profil</flux:heading>
            <flux:text class="text-sm text-zinc-500">Pindai QR atau salin tautan untuk membagikan profil ini.</flux:text>

            <canvas x-ref="qr" class="rounded-lg border border-zinc-200 dark:border-zinc-700"></canvas>

            <div class="w-full flex items-center gap-2">
                <flux:input readonly value="{{ $profileUrl }}" class="flex-1" />
                <flux:button type="button" x-on:click="copy()" icon="clipboard">
                    <span x-show="!copied">Salin</span>
                    <span x-show="copied" x-cloak>Tersalin!</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
