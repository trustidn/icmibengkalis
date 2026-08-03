<div class="flex flex-col gap-6 p-6">
    <div>
        <flux:heading size="xl">Link Partner</flux:heading>
        <flux:text class="mt-1">Mitra/kolaborator yang tampil sebagai strip logo di bagian bawah beranda. Bila tidak ada partner aktif, section otomatis disembunyikan. Tanpa logo, kartu menampilkan monogram + nama agar tetap seragam.</flux:text>
    </div>

    @if (session('partners.saved'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('partners.saved') }}" />
    @endif

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <flux:card @class(['w-full lg:w-1/3', 'ring-2 ring-blue-400' => $editingId])>
            <flux:heading size="lg">{{ $editingId ? 'Ubah Partner' : 'Tambah Partner' }}</flux:heading>
            <form wire:submit="save" class="mt-4 flex flex-col gap-4">
                <flux:input label="Nama" wire:model="name" placeholder="cth: Pemkab Bengkalis" required />
                <flux:input label="Tautan" wire:model="url" placeholder="https://..." required />
                <flux:input type="file" label="Logo (opsional)" wire:model="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                            :description="$editingId ? 'Kosongkan bila tidak ingin mengganti logo. PNG/JPG/WebP/SVG, maks. 1 MB.' : 'PNG/JPG/WebP/SVG, maks. 1 MB. Disarankan latar transparan.'" />
                <flux:input type="number" label="Urutan (opsional)" wire:model="sort_order" placeholder="0" min="0"
                            description="Angka kecil tampil lebih dulu." />
                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary">{{ $editingId ? 'Simpan Perubahan' : 'Simpan Partner' }}</flux:button>
                    @if ($editingId)
                        <flux:button type="button" variant="ghost" wire:click="cancelEdit">Batal</flux:button>
                    @endif
                    <span wire:loading wire:target="save,logo" class="text-sm text-neutral-500">Memproses…</span>
                </div>
            </form>
        </flux:card>

        <flux:card class="w-full lg:w-2/3">
            <flux:heading size="lg">Daftar Partner</flux:heading>
            <div class="mt-4 flex flex-col gap-3">
                @forelse ($partners as $partner)
                    <div wire:key="partner-{{ $partner->id }}" class="flex flex-col gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 sm:flex-row sm:items-center">
                        @if ($partner->logoUrl())
                            <img src="{{ $partner->logoUrl() }}" alt="{{ $partner->name }}" class="h-14 w-24 shrink-0 rounded object-contain bg-white p-1" />
                        @else
                            <span class="flex h-14 w-24 shrink-0 items-center justify-center rounded bg-zinc-100 font-bold text-zinc-500 dark:bg-zinc-800">{{ $partner->monogram() }}</span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-medium">{{ $partner->name }}</p>
                            <flux:text size="sm" class="truncate text-zinc-500">{{ $partner->url }}</flux:text>
                            <div class="mt-1 flex items-center gap-2">
                                <flux:badge size="sm" :color="$partner->is_active ? 'green' : 'zinc'">{{ $partner->is_active ? 'Aktif' : 'Nonaktif' }}</flux:badge>
                                <flux:text size="sm" class="text-zinc-500">Urutan: {{ $partner->sort_order }}</flux:text>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            <flux:button size="sm" variant="primary" wire:click="edit({{ $partner->id }})">Ubah</flux:button>
                            <flux:button size="sm" wire:click="toggleActive({{ $partner->id }})">{{ $partner->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</flux:button>
                            <x-confirm-delete-button name="confirm-delete-partner-{{ $partner->id }}" wire-click="deletePartner({{ $partner->id }})" message="Hapus partner ini?" />
                        </div>
                    </div>
                @empty
                    <flux:text>Belum ada partner.</flux:text>
                @endforelse
            </div>
        </flux:card>
    </div>
</div>
