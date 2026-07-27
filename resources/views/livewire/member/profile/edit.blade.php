<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">Profil Saya</flux:heading>
    <flux:text class="mt-1 text-zinc-500">NIA: {{ $member->nia }}</flux:text>

    <flux:card class="mt-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <flux:heading size="sm">Kelengkapan Profil</flux:heading>
                <flux:text class="text-sm text-zinc-500">Lengkapi profil Anda agar mudah ditemukan dan tampil profesional di halaman publik.</flux:text>
            </div>
            <span class="text-2xl font-bold {{ $completion >= 80 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $completion }}%</span>
        </div>
        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
            <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $completion }}%"></div>
        </div>
    </flux:card>

    <form wire:submit="save" class="mt-6 flex flex-col gap-4">
        <flux:card>
            <flux:heading size="lg">Foto Profil</flux:heading>
            <div class="mt-4 flex items-center gap-4">
                @if ($member->photoUrl())
                    <img src="{{ $member->photoUrl() }}" alt="{{ $member->full_name }}" class="h-20 w-20 rounded-full object-cover" />
                @else
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-zinc-200 text-zinc-400 dark:bg-zinc-700">
                        <flux:icon.user class="size-8" />
                    </div>
                @endif
                <flux:input type="file" wire:model="photo" accept="image/png,image/jpeg,image/webp"
                            description="PNG/JPG/WebP, maks. 2 MB." class="max-w-xs" />
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg">Data Diri</flux:heading>
            <div class="mt-4 flex flex-col gap-4">
                <div class="grid grid-cols-3 gap-4">
                    <flux:input label="Gelar Depan" wire:model="title_prefix" />
                    <flux:input label="Nama Lengkap" wire:model="full_name" class="col-span-2" />
                </div>
                <flux:input label="Gelar Belakang" wire:model="title_suffix" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="Jenis Kelamin" wire:model="gender">
                        <option value="">—</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </flux:select>
                    <flux:input type="date" label="Tanggal Lahir" wire:model="birth_date" />
                </div>
                <flux:input label="Tempat Lahir" wire:model="birth_place" />
                <flux:textarea label="Alamat" wire:model="address" rows="3" />

                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="Kecamatan" wire:model="district_id">
                        <option value="">—</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:input label="Instansi" wire:model="institution" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Profesi" wire:model="profession" placeholder="cth: Dosen, Wiraswasta, PNS" />
                    <flux:input label="Bidang Keahlian" wire:model="expertise" placeholder="cth: Ekonomi Syariah, Pendidikan Karakter" />
                </div>

                <flux:textarea label="Riwayat Singkat / Bio" wire:model="bio" rows="4"
                                description="Ringkasan singkat tentang Anda — akan tampil di halaman profil publik." />
            </div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg">Kontak &amp; Media Sosial</flux:heading>
            <div class="mt-4 flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input label="Website" wire:model="website" placeholder="https://..." />
                    <flux:input label="WhatsApp" wire:model="whatsapp" />
                </div>
                <flux:input label="LinkedIn" wire:model="linkedin" placeholder="https://linkedin.com/in/..." />
                <flux:checkbox wire:model="show_contact_public" label="Tampilkan kontak (WhatsApp/Website/LinkedIn) di profil publik" />
            </div>
        </flux:card>

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary">Simpan Profil</flux:button>
            <span wire:loading wire:target="save,photo" class="text-sm text-zinc-500">Memproses…</span>
            @if ($saved)
                <span wire:loading.remove class="text-sm text-green-600 dark:text-green-400">Tersimpan.</span>
            @endif
        </div>
    </form>
</div>
