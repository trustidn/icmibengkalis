<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">{{ $album->title }}</flux:heading>

    @if ($album->type === 'foto')
        <flux:card class="mt-6">
            <div class="flex gap-2 mb-4">
                <flux:button size="sm" :variant="$photoMode === 'upload' ? 'primary' : 'outline'" type="button" wire:click="$set('photoMode', 'upload')">
                    Unggah Berkas
                </flux:button>
                <flux:button size="sm" :variant="$photoMode === 'url' ? 'primary' : 'outline'" type="button" wire:click="$set('photoMode', 'url')">
                    Tautan Eksternal
                </flux:button>
            </div>

            <form wire:submit="addPhoto" class="flex flex-col gap-4">
                @if ($photoMode === 'url')
                    <flux:input label="URL Gambar" wire:model="photoUrl" placeholder="https://drive.google.com/file/d/.../view"
                                description="Google Drive, Dropbox, Flickr, atau tautan gambar langsung (mis. disalin dari Instagram/Facebook). Gambar akan diunduh dan disimpan di server." />
                @else
                    <flux:input type="file" label="Foto" wire:model="photos" multiple accept="image/png,image/jpeg,image/webp"
                                description="Bisa pilih beberapa foto sekaligus. PNG/JPG/WebP, maks. 5 MB per foto." />
                    @error('photos.*') <flux:text class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</flux:text> @enderror
                    @if (count($photos) > 0)
                        <flux:text class="text-sm text-zinc-500">{{ count($photos) }} foto siap diunggah.</flux:text>
                    @endif
                @endif

                <flux:input label="Keterangan" wire:model="photoCaption"
                            description="{{ $photoMode === 'upload' ? 'Opsional — berlaku untuk semua foto yang dipilih.' : '' }}" />

                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="photos">Tambah Foto</flux:button>
                    <span wire:loading wire:target="photos" class="text-sm font-medium text-amber-600 dark:text-amber-400">Mengunggah berkas… tunggu sampai selesai.</span>
                    <span wire:loading wire:target="addPhoto" class="text-sm text-zinc-500">Memproses…</span>
                </div>
            </form>
        </flux:card>
    @else
        <flux:card class="mt-6">
            <form wire:submit="addVideo" class="flex flex-col gap-4">
                <flux:input label="URL Video" wire:model="videoUrl" placeholder="https://www.youtube.com/watch?v=... atau https://vimeo.com/..."
                            description="Mendukung tautan YouTube dan Vimeo." />
                <flux:input label="Keterangan" wire:model="videoCaption" />
                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary">Tambah Video</flux:button>
                    <span wire:loading wire:target="addVideo" class="text-sm text-zinc-500">Memproses…</span>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
        @foreach ($items as $item)
            <div wire:key="item-{{ $item->id }}" class="relative">
                <div class="relative aspect-square w-full overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-800">
                    @if ($item->thumbnailUrl())
                        <img src="{{ $item->thumbnailUrl() }}" class="h-full w-full object-cover" alt="{{ $item->caption }}">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-zinc-400">
                            <flux:icon name="photo" class="size-8" />
                        </div>
                    @endif
                    @if ($item->isVideo())
                        <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                            <flux:icon name="play-circle" class="size-10 text-white drop-shadow" />
                        </div>
                    @endif
                </div>
                <x-confirm-delete-button name="confirm-remove-item-{{ $item->id }}" wire-click="removeItem({{ $item->id }})" message="Hapus item ini?" class="mt-2 w-full" />
            </div>
        @endforeach
    </div>
</div>
