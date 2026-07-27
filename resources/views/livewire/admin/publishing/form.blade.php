<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">{{ $post ? 'Ubah Post' : 'Tulis Post Baru' }}</flux:heading>

    <form wire:submit="save" class="mt-6 flex flex-col gap-4">
        <flux:select label="Jenis" wire:model="type">
            @foreach ($types as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </flux:select>

        <flux:input label="Judul" wire:model="title" />

        <flux:select label="Kategori" wire:model="post_category_id">
            <option value="">— Tanpa kategori —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </flux:select>

        <flux:select label="Unit Organisasi (opsional)" wire:model="org_unit_id">
            <option value="">—</option>
            @foreach ($orgUnits as $unit)
                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
        </flux:select>

        <flux:textarea label="Ringkasan" wire:model="excerpt" rows="3" />

        <x-rich-editor wire:model="body" label="Isi" />
        @error('body') <flux:text class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</flux:text> @enderror

        <div class="flex flex-col gap-2">
            <flux:input type="file" label="Gambar Utama" wire:model="featured_image" accept="image/png,image/jpeg,image/webp"
                        description="PNG/JPG/WebP, maks. 4 MB. Tampil di kartu daftar dan sampul detail." />
            @if ($post?->featuredImageUrl())
                <div class="flex items-center gap-4 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                    <img src="{{ $post->featuredImageUrl() }}" alt="Gambar utama" class="h-20 w-32 rounded object-cover" />
                    <x-confirm-delete-button name="confirm-remove-post-featured-{{ $post->id }}" wire-click="removeFeaturedImage" message="Hapus gambar utama?" label="Hapus Gambar Utama" />
                </div>
            @endif
        </div>

        <div>
            <flux:label>Tag</flux:label>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach ($tags as $tag)
                    <flux:checkbox wire:model="selectedTags" value="{{ $tag->id }}" label="{{ $tag->name }}" />
                @endforeach
            </div>
        </div>

        @if ($post && $post->status->value === 'rejected' && $post->review_note)
            <flux:callout variant="danger">
                <flux:heading size="sm">Catatan Penolakan</flux:heading>
                <flux:text class="mt-1">{{ $post->review_note }}</flux:text>
            </flux:callout>
        @endif

        <div class="flex gap-3">
            <flux:button type="submit" variant="primary">Simpan sebagai Draf</flux:button>
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
