<div class="flex flex-col gap-6 p-6 lg:flex-row">
    <flux:card class="w-full lg:w-64">
        <flux:heading size="lg">Halaman Statis</flux:heading>
        <flux:navlist class="mt-4">
            @foreach ($pages as $page)
                <flux:navlist.item
                    wire:click="select({{ $page->id }})"
                    :current="$selectedId === $page->id"
                >
                    {{ $page->title }}
                </flux:navlist.item>
            @endforeach
        </flux:navlist>
    </flux:card>

    <flux:card class="w-full">
        @if ($selectedId)
            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input label="Judul" wire:model="title" />

                <x-rich-editor wire:model="body" label="Konten" :key="'editor-page-'.$selectedId" />
                @error('body') <flux:text class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</flux:text> @enderror

                @php $selectedPage = $pages->firstWhere('id', $selectedId); @endphp
                <div class="flex flex-col gap-2">
                    <flux:input type="file" label="Gambar Utama" wire:model="featured_image" accept="image/png,image/jpeg,image/webp"
                                description="PNG/JPG/WebP, maks. 4 MB. Dipakai a.l. untuk foto Sambutan Ketua di beranda." />
                    @if ($selectedPage?->featuredImageUrl())
                        <div class="flex items-center gap-4 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                            <img src="{{ $selectedPage->featuredImageUrl() }}" alt="Gambar utama" class="h-20 w-32 rounded object-cover" />
                            <x-confirm-delete-button name="confirm-remove-page-featured-{{ $selectedId }}" wire-click="removeFeaturedImage" message="Hapus gambar utama halaman ini?" label="Hapus Gambar Utama" />
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary">Simpan</flux:button>
                    @if ($saved)
                        <span class="text-sm text-green-600 dark:text-green-400">Tersimpan.</span>
                    @endif
                </div>
            </form>
        @else
            <flux:text>Pilih halaman di sebelah kiri untuk mengedit.</flux:text>
        @endif
    </flux:card>
</div>
