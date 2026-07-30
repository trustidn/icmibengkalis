<div class="flex flex-col gap-6 p-6">
    <div>
        <flux:heading size="xl">Poster Ucapan</flux:heading>
        <flux:text class="mt-1">Poster ucapan di beranda (hari jadi, hari kemerdekaan, dll.). Bila tidak ada poster yang tayang, section di beranda otomatis disembunyikan.</flux:text>
    </div>

    @if (session('posters.saved'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('posters.saved') }}" />
    @endif

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <flux:card class="w-full lg:w-1/3">
            <flux:heading size="lg">Tambah Poster</flux:heading>
            <form wire:submit="save" class="mt-4 flex flex-col gap-4">
                <flux:input label="Judul" wire:model="title" placeholder="cth: Dirgahayu RI ke-81" required
                            description="Dipakai sebagai teks alternatif gambar." />
                <flux:input type="file" label="Gambar Poster" wire:model="image" accept="image/png,image/jpeg,image/webp"
                            description="PNG/JPG/WebP, maks. 4 MB. Disarankan format landscape/banner." />
                <flux:input label="Tautan (opsional)" wire:model="link_url" placeholder="https://..." />
                <div class="grid grid-cols-2 gap-3">
                    <flux:input type="date" label="Mulai tayang" wire:model="starts_at" description="Kosongkan = langsung" />
                    <flux:input type="date" label="Berakhir" wire:model="ends_at" description="Kosongkan = tanpa batas" />
                </div>
                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary">Simpan Poster</flux:button>
                    <span wire:loading wire:target="save,image" class="text-sm text-neutral-500">Memproses…</span>
                </div>
            </form>
        </flux:card>

        <flux:card class="w-full lg:w-2/3">
            <flux:heading size="lg">Daftar Poster</flux:heading>
            <div class="mt-4 flex flex-col gap-3">
                @forelse ($posters as $poster)
                    @php
                        $visible = $poster->is_active
                            && (! $poster->starts_at || $poster->starts_at->lte(today()))
                            && (! $poster->ends_at || $poster->ends_at->gte(today()));
                    @endphp
                    <div wire:key="poster-{{ $poster->id }}" class="flex flex-col gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 sm:flex-row sm:items-center">
                        @if ($poster->imageUrl())
                            <img src="{{ $poster->imageUrl() }}" alt="{{ $poster->title }}" class="h-20 w-36 shrink-0 rounded object-cover" />
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-medium">{{ $poster->title }}</p>
                            <flux:text size="sm" class="text-zinc-500">
                                {{ $poster->starts_at?->format('d/m/Y') ?? 'Langsung' }} — {{ $poster->ends_at?->format('d/m/Y') ?? 'Tanpa batas' }}
                            </flux:text>
                            <div class="mt-1 flex gap-2">
                                @if ($visible)
                                    <flux:badge size="sm" color="green">Tayang</flux:badge>
                                @elseif (! $poster->is_active)
                                    <flux:badge size="sm" color="zinc">Nonaktif</flux:badge>
                                @else
                                    <flux:badge size="sm" color="amber">Di luar masa tayang</flux:badge>
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <flux:button size="sm" wire:click="toggleActive({{ $poster->id }})">{{ $poster->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</flux:button>
                            <x-confirm-delete-button name="confirm-delete-poster-{{ $poster->id }}" wire-click="deletePoster({{ $poster->id }})" message="Hapus poster ini?" />
                        </div>
                    </div>
                @empty
                    <flux:text>Belum ada poster.</flux:text>
                @endforelse
            </div>
        </flux:card>
    </div>
</div>
