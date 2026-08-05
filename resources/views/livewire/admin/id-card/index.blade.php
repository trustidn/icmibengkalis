<div class="p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">ID Card Kegiatan</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Buat kegiatan ber-ID card — seluruh anggota aktif otomatis mendapatkan kartunya di dashboard masing-masing, tanpa mendaftar.</flux:text>
        </div>
        <flux:button wire:click="create" variant="primary">Kegiatan Baru</flux:button>
    </div>

    @if ($showForm)
        <flux:card class="mt-6">
            <flux:heading size="lg">{{ $editingId ? 'Ubah Kegiatan' : 'Kegiatan Baru' }}</flux:heading>
            <form wire:submit="save" class="mt-4 flex flex-col gap-4">
                <flux:input label="Nama Kegiatan" wire:model="name" placeholder="cth: Pelantikan Pengurus 2026-2031" />
                <flux:input type="date" label="Tanggal Kegiatan (opsional)" wire:model="event_date" />
                <flux:input type="file" label="{{ $editingId ? 'Ganti Desain Latar (opsional)' : 'Desain Latar Kartu' }}" wire:model="background" accept="image/png,image/jpeg,image/webp"
                            description="Potret rasio 54:85,6 (mis. 1080x1712 px), maks. 5 MB. Sisakan zona overlay: 12-21mm nama kegiatan, 21-55mm foto, 55-67mm nama (teks putih), 67-83mm QR." />
                @error('background') <flux:text class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</flux:text> @enderror

                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="background">{{ $editingId ? 'Simpan Perubahan' : 'Simpan' }}</flux:button>
                    <flux:button type="button" wire:click="cancelEdit">Batal</flux:button>
                    <span wire:loading wire:target="background" class="text-sm font-medium text-amber-600 dark:text-amber-400">Mengunggah berkas… tunggu sampai selesai.</span>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="mt-6 flex flex-col gap-3">
        @forelse ($events as $event)
            <div wire:key="idcard-event-{{ $event->id }}" class="flex flex-wrap items-center gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                @if ($event->backgroundUrl())
                    <img src="{{ $event->backgroundUrl() }}" alt="" class="h-20 w-auto max-w-14 rounded object-cover" />
                @else
                    <div class="flex h-20 w-14 items-center justify-center rounded bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                        <flux:icon.identification class="size-6" />
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $event->name }}</p>
                    <p class="text-sm text-zinc-500">
                        @if ($event->event_date) {{ $event->event_date->translatedFormat('d F Y') }} · @endif
                        Kartu otomatis untuk {{ $memberCount }} anggota aktif
                    </p>
                    <flux:badge size="sm" :color="$event->is_active ? 'lime' : 'zinc'" class="mt-1">{{ $event->is_active ? 'Dibuka' : 'Ditutup' }}</flux:badge>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($memberCount > 0)
                        <flux:button :href="route('admin.idcard.print-all', $event)" size="sm" variant="primary">Cetak Semua (PDF)</flux:button>
                    @endif
                    <flux:button wire:click="edit({{ $event->id }})" size="sm">Ubah</flux:button>
                    <flux:button wire:click="toggleActive({{ $event->id }})" size="sm">{{ $event->is_active ? 'Tutup' : 'Buka' }}</flux:button>
                    <x-confirm-delete-button name="confirm-delete-idcard-{{ $event->id }}" wire-click="delete({{ $event->id }})" message="Hapus kegiatan ini?" />
                </div>
            </div>
        @empty
            <p class="text-zinc-500">Belum ada kegiatan. Tekan "Kegiatan Baru" untuk membuat.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $events->links() }}</div>
</div>
