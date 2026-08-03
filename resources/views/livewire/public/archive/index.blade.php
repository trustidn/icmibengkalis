<div>
    <x-public.page-header eyebrow="Pengetahuan" title="Arsip Digital" subtitle="Dokumen, surat keputusan, dan publikasi resmi ICMI Kabupaten Bengkalis." />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        @auth
            <div class="mb-8 flex justify-end">
                <a href="{{ route('archive.upload') }}" wire:navigate
                   class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-full font-label-lg text-label-lg hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 transition-all">
                    <span class="material-symbols-outlined text-[20px]">upload_file</span>
                    Unggah Dokumen
                </a>
            </div>
        @endauth
        <div class="flex flex-wrap gap-3 mb-10">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari judul..."
                   class="flex-1 min-w-[200px] max-w-xs bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary" />

            <select wire:model.live="category_id"
                    class="bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="doc_type"
                    class="bg-white border border-outline-variant/40 rounded-lg px-4 py-2.5 font-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <option value="">Semua Jenis</option>
                @foreach ($docTypes as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>

        @if ($documents->isNotEmpty())
            <div class="grid gap-gutter sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($documents as $document)
                    @php
                        $bisaAkses = \Illuminate\Support\Facades\Gate::forUser(auth()->user())->allows('view', $document);
                        $khususAnggota = $document->access_level !== \App\Enums\DocumentAccessLevel::Publik;
                    @endphp

                    @if ($bisaAkses)
                        <a wire:key="document-{{ $document->id }}" href="{{ route('archive.show', $document->slug) }}" wire:navigate
                           class="group bg-white rounded-xl border border-outline-variant/30 card-shadow-hover transition-all duration-300 overflow-hidden flex flex-col">
                            <x-public.image-placeholder icon="description" />
                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="text-secondary font-bold text-[12px] uppercase tracking-widest">{{ $document->doc_type->label() }}</span>
                                    @if ($khususAnggota)
                                        <span class="inline-flex items-center gap-1 bg-primary-container/15 text-primary px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide">
                                            <span class="material-symbols-outlined text-[13px]">lock_open</span> {{ $document->access_level->label() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center bg-surface-container-low text-on-surface-variant px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide">Publik</span>
                                    @endif
                                </div>
                                <h3 class="font-headline-md text-[20px] leading-tight text-on-surface group-hover:text-primary transition-colors mb-3">{{ $document->title }}</h3>
                                <p class="font-body-md text-on-surface-variant line-clamp-2 opacity-80 flex-1">{{ $document->description }}</p>
                            </div>
                        </a>
                    @else
                        {{-- Dokumen khusus anggota untuk pengunjung tanpa hak akses: kartu terkunci --}}
                        <div wire:key="document-{{ $document->id }}"
                             class="bg-white rounded-xl border border-outline-variant/30 overflow-hidden flex flex-col opacity-95">
                            <div class="relative">
                                <x-public.image-placeholder icon="description" class="opacity-50" />
                                <span class="absolute inset-0 flex items-center justify-center">
                                    <span class="w-12 h-12 rounded-full bg-on-surface/70 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-white text-[24px]">lock</span>
                                    </span>
                                </span>
                            </div>
                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span class="text-secondary font-bold text-[12px] uppercase tracking-widest">{{ $document->doc_type->label() }}</span>
                                    <span class="inline-flex items-center gap-1 bg-secondary-container/25 text-on-secondary-container px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wide">
                                        <span class="material-symbols-outlined text-[13px]">lock</span> Khusus Anggota
                                    </span>
                                </div>
                                <h3 class="font-headline-md text-[20px] leading-tight text-on-surface/70 mb-3">{{ $document->title }}</h3>
                                <p class="font-body-md text-on-surface-variant line-clamp-2 opacity-60 flex-1">{{ $document->description }}</p>
                                <div class="mt-4 pt-4 border-t border-outline-variant/20">
                                    @guest
                                        <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 text-primary font-bold text-sm hover:gap-3 transition-all">
                                            <span class="material-symbols-outlined text-[18px]">login</span> Masuk untuk mengakses
                                        </a>
                                    @else
                                        <span class="font-body-md text-sm text-on-surface-variant">Hanya untuk anggota ICMI Bengkalis.</span>
                                    @endguest
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="font-body-md text-on-surface-variant text-center py-16">Belum ada dokumen yang bisa diakses.</p>
        @endif

        <div class="mt-12">{{ $documents->links() }}</div>
    </div>
</div>
