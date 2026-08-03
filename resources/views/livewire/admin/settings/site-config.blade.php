<div class="flex flex-col gap-6 p-6">
    <div>
        <flux:heading size="xl">Konfigurasi Web</flux:heading>
        <flux:text class="mt-1">Identitas situs, kontak, logo, gambar hero, dan favicon. Dapat diakses Super Admin dan Admin Web.</flux:text>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <flux:card class="w-full lg:w-1/2">
            <flux:heading size="lg">Identitas &amp; Kontak</flux:heading>
            <div class="mt-4 flex flex-col gap-4">
                <flux:input label="Nama Website" wire:model="site_name" required />
                <flux:textarea label="Tagline" wire:model="tagline" rows="2" />
                <flux:textarea label="Alamat Sekretariat" wire:model="address" rows="2" />
                <flux:input label="Email" type="email" wire:model="email" />
                <flux:input label="Telepon / WhatsApp" wire:model="phone" />
                <flux:input label="Facebook (URL)" wire:model="facebook" placeholder="https://facebook.com/..." />
                <flux:input label="Instagram (URL)" wire:model="instagram" placeholder="https://instagram.com/..." />
                <flux:input label="YouTube (URL)" wire:model="youtube" placeholder="https://youtube.com/..." />

                <flux:separator class="my-2" />

                <flux:switch wire:model="registration_enabled" label="Aktifkan Pendaftaran Akun"
                             description="Tautan pendaftaran tidak pernah ditampilkan di situs publik. Bila dinonaktifkan, halaman /register mengembalikan 404." />
            </div>
        </flux:card>

        <flux:card class="w-full lg:w-1/2">
            <flux:heading size="lg">Logo, Gambar Hero &amp; Favicon</flux:heading>
            <div class="mt-4 flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <flux:input type="file" label="Logo Website" wire:model="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                description="PNG/JPG/WebP/SVG, maks. 2 MB. Menggantikan logo lama." />
                    @if ($settings->logoUrl())
                        <div class="flex items-center gap-4 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                            <img src="{{ $settings->logoUrl() }}" alt="Logo saat ini" class="h-12 w-auto" />
                            <x-confirm-delete-button name="confirm-remove-logo" wire-click="removeLogo" message="Hapus logo saat ini? Situs akan kembali memakai logo bawaan." label="Hapus Logo" />
                        </div>
                    @else
                        <flux:text class="text-sm">Belum ada logo — situs memakai logo bawaan.</flux:text>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    <flux:input type="file" label="Gambar Hero Beranda" wire:model="hero" accept="image/png,image/jpeg,image/webp"
                                description="PNG/JPG/WebP, maks. 4 MB. Tampil pada panel kanan hero beranda." />
                    @if ($settings->heroUrl())
                        <div class="flex flex-col gap-3 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                            <img src="{{ $settings->heroUrl() }}" alt="Gambar hero saat ini" class="max-h-40 w-full rounded object-cover" />
                            <x-confirm-delete-button name="confirm-remove-hero" wire-click="removeHero" message="Hapus gambar hero? Beranda akan kembali menampilkan panel bawaan." label="Hapus Gambar Hero" />
                        </div>
                    @else
                        <flux:text class="text-sm">Belum ada gambar hero — beranda memakai panel bawaan.</flux:text>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    <flux:input type="file" label="Favicon" wire:model="favicon" accept="image/png,image/svg+xml,image/x-icon,image/vnd.microsoft.icon"
                                description="ICO/PNG/SVG, maks. 512 KB. Ikon di tab browser — idealnya persegi (mis. 32×32 atau 512×512 px)." />
                    @if ($settings->faviconUrl())
                        <div class="flex items-center gap-4 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                            <img src="{{ $settings->faviconUrl() }}" alt="Favicon saat ini" class="h-8 w-8 object-contain" />
                            <x-confirm-delete-button name="confirm-remove-favicon" wire-click="removeFavicon" message="Hapus favicon? Situs akan kembali memakai ikon bawaan browser." label="Hapus Favicon" />
                        </div>
                    @else
                        <flux:text class="text-sm">Belum ada favicon — browser memakai ikon bawaan.</flux:text>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="logo,hero,favicon">Simpan Konfigurasi</flux:button>
                    <span wire:loading wire:target="logo,hero,favicon" class="text-sm font-medium text-amber-600 dark:text-amber-400">Mengunggah berkas… tunggu sampai selesai.</span>
                    <span wire:loading wire:target="save,logo,hero,favicon" class="text-sm text-neutral-500">Memproses…</span>
                    @if ($saved)
                        <span wire:loading.remove class="text-sm text-green-600 dark:text-green-400">Tersimpan.</span>
                    @endif
                </div>
            </div>
        </flux:card>
    </form>
</div>
