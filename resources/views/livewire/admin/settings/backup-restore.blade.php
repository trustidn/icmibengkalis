<div class="flex flex-col gap-6 p-6">
    <div>
        <flux:heading size="xl">Backup &amp; Restore Database</flux:heading>
        <flux:text class="mt-1">Unduh salinan seluruh database, atau pulihkan dari file backup — untuk pemindahan server dan cadangan rutin. Hanya dapat diakses pemegang izin konfigurasi.</flux:text>
    </div>

    @unless ($supported)
        <flux:callout variant="warning" icon="exclamation-triangle" heading="Fitur ini butuh database MySQL/MariaDB."
                      text="Lingkungan saat ini memakai driver lain (mis. SQLite di dev Herd). Jalankan lewat stack Docker (dev/produksi) untuk menggunakannya." />
    @else
        @if ($status)
            <flux:callout variant="success" icon="check-circle" heading="{{ $status }}" />
        @endif
        @if ($error)
            <flux:callout variant="danger" icon="x-circle" heading="{{ $error }}" />
        @endif

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <flux:card class="w-full lg:w-1/2">
                <flux:heading size="lg">Unduh Backup</flux:heading>
                <flux:text class="mt-2">Menghasilkan file <code>backup-icmi-&lt;tanggal&gt;.sql.gz</code> berisi seluruh tabel database (anggota, artikel, arsip, pengaturan, dll.).</flux:text>
                <flux:text class="mt-2" size="sm">Catatan: file unggahan (foto, dokumen arsip) TIDAK ikut — untuk backup penuh termasuk file, gunakan <code>make prod-backup</code> di server (lihat docs/11 §11.3.5).</flux:text>
                <div class="mt-5">
                    <flux:button :href="route('admin.backup.download')" icon="arrow-down-tray" variant="primary">
                        Unduh Backup Database
                    </flux:button>
                </div>
            </flux:card>

            <flux:card class="w-full lg:w-1/2 border-red-200 dark:border-red-900">
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">Restore Database</flux:heading>
                <flux:text class="mt-2"><strong>PERINGATAN:</strong> seluruh isi database saat ini akan DIHAPUS dan diganti dengan isi file backup. Tindakan ini tidak bisa dibatalkan. Pastikan file berasal dari portal ini dan APP_KEY server sama dengan saat backup dibuat.</flux:text>
                <form wire:submit="restore" class="mt-4 flex flex-col gap-4">
                    <flux:input type="file" label="File Backup (.sql / .sql.gz)" wire:model="dumpFile" accept=".sql,.gz" />
                    <flux:input label='Ketik "PULIHKAN" untuk konfirmasi' wire:model="confirmText" placeholder="PULIHKAN" autocomplete="off" />
                    <div class="flex items-center gap-3">
                        <flux:button type="submit" variant="danger" icon="arrow-uturn-left">
                            Pulihkan Database
                        </flux:button>
                        <span wire:loading wire:target="restore,dumpFile" class="text-sm text-neutral-500">Memproses… jangan tutup halaman.</span>
                    </div>
                </form>
            </flux:card>
        </div>
    @endunless
</div>
