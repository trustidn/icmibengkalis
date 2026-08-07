<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">{{ $member ? 'Ubah Anggota' : 'Tambah Anggota' }}</flux:heading>
    @if ($member)
        <flux:text class="mt-1 text-zinc-500">NIA: {{ $member->nia }}</flux:text>
    @endif

    <form wire:submit="save" class="mt-6 flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <flux:heading size="sm">Foto Profil</flux:heading>
            <div class="flex items-center gap-4">
                @if ($member?->photoUrl())
                    <img src="{{ $member->photoUrl() }}" alt="{{ $member->full_name }}" class="h-16 w-16 rounded-full object-cover" />
                @else
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <span class="material-symbols-outlined text-zinc-400">person</span>
                    </span>
                @endif
                <div class="flex flex-1 flex-col gap-2">
                    <flux:input type="file" wire:model="photo" accept="image/png,image/jpeg,image/webp"
                                description="PNG/JPG/WebP, maks. 2 MB. Menggantikan foto lama saat disimpan." />
                    @if ($member?->photoUrl())
                        <x-confirm-delete-button name="confirm-remove-member-photo" wire-click="removePhoto" message="Hapus foto profil anggota ini?" label="Hapus Foto" />
                    @endif
                </div>
            </div>
        </div>

        <flux:separator class="my-1" />

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

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="Status Keanggotaan" wire:model="status">
                @foreach ($statuses as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </flux:select>
            <flux:input type="date" label="Tanggal Bergabung" wire:model="joined_at" />
        </div>

        <flux:textarea label="Riwayat Singkat / Bio" wire:model="bio" rows="4" />

        <flux:checkbox wire:model="show_contact_public" label="Tampilkan nomor WhatsApp di profil publik"
                       description="Tautan lain (website & media sosial) selalu tampil. Kelola tautan di kartu bawah setelah anggota tersimpan." />

        <div class="flex items-center gap-3">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="photo">Simpan</flux:button>
                    <span wire:loading wire:target="photo" class="text-sm font-medium text-amber-600 dark:text-amber-400">Mengunggah berkas… tunggu sampai selesai.</span>
            <span wire:loading wire:target="save,photo" class="text-sm text-zinc-500">Memproses…</span>
        </div>
    </form>

    @if ($member)
        <flux:card class="mt-8">
            <flux:heading size="lg">Tautan &amp; Media Sosial</flux:heading>

            <form wire:submit="addLink" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <flux:select label="Jenis" wire:model="linkType">
                    @foreach ($linkTypes as $nilai => $labelJenis)
                        <option value="{{ $nilai }}">{{ $labelJenis }}</option>
                    @endforeach
                </flux:select>
                <flux:input label="Label (opsional)" wire:model="linkLabel" placeholder="cth: Toko Online" />
                <flux:input label="Alamat / Nomor" wire:model="linkValue" placeholder="https://... atau 08..." />
                <div class="flex items-end"><flux:button type="submit" size="sm">Tambah</flux:button></div>
            </form>
            @error('linkValue') <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text> @enderror

            <div class="mt-4 flex flex-col gap-2">
                @forelse ($memberLinks as $link)
                    <div wire:key="link-{{ $link->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:text class="min-w-0 truncate"><span class="font-bold">{{ $link->displayLabel() }}</span><span class="mx-1 text-zinc-400">&middot;</span>{{ $link->value }}</flux:text>
                        <flux:button wire:click="deleteLink({{ $link->id }})" size="sm" variant="danger">Hapus</flux:button>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">Belum ada tautan.</flux:text>
                @endforelse
            </div>
        </flux:card>
    @endif

    @if ($member)
        <flux:card class="mt-8">
            <flux:heading size="lg">Riwayat Pendidikan</flux:heading>

            <form wire:submit="addEducation" class="mt-4 grid grid-cols-4 gap-3">
                <flux:select label="Jenjang" wire:model="eduLevel">
                    @foreach ($educationLevels as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </flux:select>
                <flux:input label="Institusi" wire:model="eduInstitution" />
                <flux:input label="Jurusan" wire:model="eduMajor" />
                <flux:input type="number" label="Tahun Lulus" wire:model="eduGraduatedYear" />
                <div class="col-span-4"><flux:button type="submit" size="sm">Tambah Pendidikan</flux:button></div>
            </form>

            <div class="mt-4 flex flex-col gap-2">
                @forelse ($educations as $education)
                    <div wire:key="edu-{{ $education->id }}" class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:text>{{ $education->level->label() }} — {{ $education->institution }} @if($education->major) ({{ $education->major }}) @endif @if($education->graduated_year) · {{ $education->graduated_year }} @endif</flux:text>
                        <flux:button wire:click="deleteEducation({{ $education->id }})" size="sm" variant="danger">Hapus</flux:button>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">Belum ada riwayat pendidikan.</flux:text>
                @endforelse
            </div>
        </flux:card>
    @endif
</div>
