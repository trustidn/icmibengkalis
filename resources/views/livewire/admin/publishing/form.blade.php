<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">{{ $post ? 'Ubah Post' : 'Tulis Post Baru' }}</flux:heading>

    <form wire:submit="save" class="mt-6 flex flex-col gap-4">
        <flux:select label="Jenis" wire:model="type">
            @foreach ($types as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </flux:select>

        <flux:input label="Judul" wire:model="title" />

        <flux:input type="date" label="Tanggal Artikel" wire:model="published_at"
                    description="Menentukan urutan tampil artikel. Kosongkan agar terisi otomatis dengan tanggal terbit." />

        <x-rich-editor wire:model="body" label="Isi" />
        @error('body') <flux:text class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</flux:text> @enderror
        <flux:text class="text-sm text-zinc-500">Ringkasan artikel dibuat otomatis dari kalimat-kalimat awal paragraf pertama.</flux:text>

        <flux:input label="Tag / Kata Kunci" wire:model="tags" placeholder="cth: ekonomi syariah, umkm, pendidikan"
                    description="Pisahkan dengan koma. Tampil di halaman artikel dan menjadi hashtag saat dibagikan." />

        <div class="flex flex-col gap-2">
            <flux:input type="file" label="Gambar Utama" wire:model="featured_image" accept="image/png,image/jpeg,image/webp"
                        description="PNG/JPG/WebP, maks. 4 MB. Tampil di kartu daftar dan sampul detail." />
            @if ($post?->featuredImageUrl())
                <div class="flex items-center gap-4 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                    <img src="{{ $post->featuredImageUrl() }}" alt="Gambar utama" class="h-20 w-32 rounded object-cover" />
                    <x-confirm-delete-button name="confirm-remove-post-featured-{{ $post->id }}" wire-click="removeFeaturedImage" message="Hapus gambar utama?" label="Hapus Gambar Utama" />
                </div>
            @endif
            <flux:input label="Caption Gambar Utama (opsional)" wire:model="featured_caption"
                        placeholder="cth: Suasana rapat perdana pengurus di Aula BAPPEDA"
                        description="Tampil di bawah gambar utama pada halaman artikel." />
        </div>

        @if ($post && $post->status->value === 'rejected' && $post->review_note)
            <flux:callout variant="danger">
                <flux:heading size="sm">Catatan Penolakan</flux:heading>
                <flux:text class="mt-1">{{ $post->review_note }}</flux:text>
            </flux:callout>
        @endif

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="featured_image">Simpan sebagai Draf</flux:button>
                    <span wire:loading wire:target="featured_image" class="text-sm font-medium text-amber-600 dark:text-amber-400">Mengunggah berkas… tunggu sampai selesai.</span>
            @if (! $post || in_array($post->status->value, ['draft', 'rejected']))
                <flux:button type="button" wire:click="submitForReview">Simpan & Ajukan Review</flux:button>
            @endif
        </div>
    </form>

    @if ($revisions->isNotEmpty())
        <flux:card class="mt-8">
            <flux:heading size="lg">Riwayat Revisi</flux:heading>
            <div class="mt-3 flex flex-col gap-2">
                @foreach ($revisions as $revision)
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $revision->editor->name }} — {{ $revision->created_at->translatedFormat('d F Y, H:i') }}
                    </flux:text>
                @endforeach
            </div>
        </flux:card>
    @endif
</div>
