<div>
    <div class="relative bg-gradient-to-b from-white to-surface-container-low border-b border-outline-variant/20 px-margin-mobile md:px-margin-desktop pt-16 pb-12 md:pt-24 md:pb-16 overflow-hidden">
        <div class="absolute inset-0 hero-pattern -z-10"></div>
        <div class="max-w-3xl mx-auto">
            <span class="text-secondary font-bold text-[12px] uppercase tracking-widest">{{ $document->doc_type->label() }}</span>
            <h1 class="mt-3 font-headline-lg text-headline-lg md:font-display-lg md:text-display-lg text-on-surface leading-tight">{{ $document->title }}</h1>
            @if ($document->document_number)
                <p class="mt-4 font-label-lg text-label-lg text-on-surface-variant">No. {{ $document->document_number }}</p>
            @endif
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">{{ $document->description }}</p>

        @if ($document->uploader)
            <p class="mt-4 font-body-md text-sm text-on-surface-variant opacity-80">
                Diunggah oleh {{ $document->uploader->name }} · {{ $document->created_at->translatedFormat('d F Y') }}
            </p>
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-4">
            <a href="{{ route('archive.download', $document) }}"
               class="inline-flex items-center gap-3 bg-primary text-on-primary px-8 py-3.5 rounded-full font-label-lg text-label-lg hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 transition-all active:scale-95">
                <span class="material-symbols-outlined text-[20px]">download</span>
                Unduh (v{{ $document->current_version }})
            </a>

            @if (auth()->user()?->can('delete', $document))
                <flux:modal.trigger name="confirm-delete-document">
                    <button type="button" class="inline-flex items-center gap-2 border-2 border-red-200 text-red-600 px-6 py-3 rounded-full font-label-lg text-label-lg hover:bg-red-50 transition-all">
                        <span class="material-symbols-outlined text-[20px]">delete</span>
                        Hapus
                    </button>
                </flux:modal.trigger>
                <flux:modal name="confirm-delete-document" class="max-w-md">
                    <flux:heading size="lg">Hapus dokumen ini?</flux:heading>
                    <flux:text class="mt-2">"{{ $document->title }}" akan dihapus dari arsip beserta seluruh versinya.</flux:text>
                    <div class="mt-6 flex justify-end gap-2">
                        <flux:modal.close><flux:button variant="ghost">Batal</flux:button></flux:modal.close>
                        <flux:button variant="danger" wire:click="delete">Hapus</flux:button>
                    </div>
                </flux:modal>
            @endif
        </div>

        @php
            $mime = $document->latestVersion()?->getFileMedia()?->mime_type;
        @endphp

        @if ($mime === 'application/pdf')
            <div class="mt-10 rounded-xl overflow-hidden border border-outline-variant/30">
                <iframe
                    src="{{ route('archive.download', $document) }}?preview=1"
                    class="h-[70vh] w-full"
                ></iframe>
            </div>
        @elseif (str_starts_with((string) $mime, 'image/'))
            <img src="{{ route('archive.download', $document) }}?preview=1" class="mt-10 w-full rounded-xl border border-outline-variant/30" alt="{{ $document->title }}">
        @endif

        <div class="mt-10">
            <a href="{{ route('archive.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all w-fit">
                <span class="material-symbols-outlined text-[18px]">west</span> Kembali ke Arsip
            </a>
        </div>
    </div>
</div>
