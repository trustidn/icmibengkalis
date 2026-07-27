<div>
    <x-public.page-header eyebrow="Pengetahuan" title="Arsip Digital" subtitle="Dokumen, surat keputusan, dan publikasi resmi ICMI Kabupaten Bengkalis." />

    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
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
                    <a wire:key="document-{{ $document->id }}" href="{{ route('archive.show', $document->slug) }}" wire:navigate
                       class="group bg-white rounded-xl border border-outline-variant/30 card-shadow-hover transition-all duration-300 overflow-hidden flex flex-col">
                        <x-public.image-placeholder icon="description" />
                        <div class="p-6 flex flex-col flex-1">
                            <span class="text-secondary font-bold text-[12px] uppercase tracking-widest mb-3">{{ $document->doc_type->label() }}</span>
                            <h3 class="font-headline-md text-[20px] leading-tight text-on-surface group-hover:text-primary transition-colors mb-3">{{ $document->title }}</h3>
                            <p class="font-body-md text-on-surface-variant line-clamp-2 opacity-80 flex-1">{{ $document->description }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <p class="font-body-md text-on-surface-variant text-center py-16">Belum ada dokumen yang bisa diakses.</p>
        @endif

        <div class="mt-12">{{ $documents->links() }}</div>
    </div>
</div>
