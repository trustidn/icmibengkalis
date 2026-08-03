<div class="mx-auto max-w-2xl p-6">
    <flux:heading size="xl">{{ $post ? 'Ubah Tulisan' : 'Tulis Berita/Artikel/Opini' }}</flux:heading>
    <flux:text class="mt-1 text-zinc-500">
        Tulisan Anda langsung tayang begitu dikirim. Pengurus berhak menyunting atau menghapus tulisan yang tidak sesuai aturan.
    </flux:text>

    @if ($submitted)
        <flux:callout class="mt-6" variant="success">Tulisan Anda telah tayang.</flux:callout>
    @endif

    @if ($post && $post->status->value === 'rejected' && $post->review_note)
        <flux:callout class="mt-6" variant="danger">
            <flux:heading size="sm">Catatan Penolakan</flux:heading>
            <flux:text class="mt-1">{{ $post->review_note }}</flux:text>
        </flux:callout>
    @endif

    <form wire:submit="submit" class="mt-6 flex flex-col gap-4">
        <flux:select label="Jenis" wire:model="type">
            @foreach ($types as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </flux:select>

        <flux:input label="Judul" wire:model="title" />
        <flux:textarea label="Ringkasan" wire:model="excerpt" rows="3" />

        <x-rich-editor wire:model="body" label="Isi" />
        @error('body') <flux:text class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</flux:text> @enderror

        <flux:input type="file" label="Gambar Utama (opsional)" wire:model="featured_image" accept="image/png,image/jpeg,image/webp"
                    description="PNG/JPG/WebP, maks. 4 MB." />

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="featured_image">{{ $post ? 'Simpan Perubahan' : 'Kirim' }}</flux:button>
                    <span wire:loading wire:target="featured_image" class="text-sm font-medium text-amber-600 dark:text-amber-400">Mengunggah berkas… tunggu sampai selesai.</span>
            @if ($post)
                <flux:button :href="route('member.posts.index')" variant="ghost" wire:navigate>Batal</flux:button>
            @endif
        </div>
    </form>
</div>
