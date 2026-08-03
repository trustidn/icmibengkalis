<div>
    <x-public.page-header eyebrow="Arsip Digital" title="Unggah Dokumen" subtitle="Bagikan dokumen ke arsip digital ICMI Bengkalis. Dokumen yang Anda unggah dapat Anda hapus sendiri kapan saja." />

    <div class="max-w-2xl mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <div class="bg-white rounded-xl border border-outline-variant/30 p-6 md:p-8 card-shadow">
            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input label="Judul Dokumen" wire:model="title" placeholder="cth: Materi Kajian Ekonomi Syariah" required />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select label="Jenis Dokumen" wire:model="doc_type" required>
                        <option value="">— Pilih jenis —</option>
                        @foreach ($docTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select label="Kategori (opsional)" wire:model="document_category_id">
                        <option value="">—</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:textarea label="Deskripsi (opsional)" wire:model="description" rows="3" />

                <flux:select label="Siapa yang boleh mengakses?" wire:model="access_level"
                             description="Publik: semua pengunjung situs. Anggota: hanya anggota ICMI yang login.">
                    <option value="publik">Publik</option>
                    <option value="anggota">Khusus Anggota</option>
                </flux:select>

                <flux:input type="file" label="Berkas" wire:model="file"
                            description="PDF/dokumen/gambar, maks. 20 MB." />

                <div class="flex items-center gap-3 pt-2">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="file">Unggah Dokumen</flux:button>
                    <span wire:loading wire:target="file" class="text-sm font-medium text-amber-600 dark:text-amber-400">Mengunggah berkas… tunggu sampai selesai.</span>
                    <span wire:loading wire:target="save,file" class="text-sm text-on-surface-variant">Memproses…</span>
                </div>
            </form>
        </div>

        <div class="mt-8">
            <a href="{{ route('archive.index') }}" wire:navigate class="text-primary font-bold flex items-center gap-2 hover:gap-3 transition-all w-fit">
                <span class="material-symbols-outlined text-[18px]">west</span> Kembali ke Arsip
            </a>
        </div>
    </div>
</div>
