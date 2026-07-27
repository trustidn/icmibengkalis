<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">{{ $document ? 'Kelola Dokumen' : 'Unggah Dokumen Baru' }}</flux:heading>

    <form wire:submit="save" class="mt-6 flex flex-col gap-4">
        <flux:input label="Judul" wire:model="title" />
        <flux:input label="Nomor Surat/SK (opsional)" wire:model="document_number" />

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="Jenis" wire:model="doc_type">
                @foreach ($docTypes as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </flux:select>
            <flux:select label="Kategori" wire:model="document_category_id">
                <option value="">—</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:textarea label="Deskripsi" wire:model="description" rows="3" />

        <div class="grid grid-cols-2 gap-4">
            <flux:select label="Tingkat Akses" wire:model="access_level">
                @foreach ($accessLevels as $case)
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </flux:select>
            <flux:input type="date" label="Tanggal Dokumen" wire:model="document_date" />
        </div>

        @unless ($document)
            <flux:input type="file" label="File" wire:model="file" />
        @endunless

        <div><flux:button type="submit" variant="primary">Simpan</flux:button></div>
    </form>

    @if ($document)
        <flux:card class="mt-8">
            <flux:heading size="lg">Unggah Versi Baru</flux:heading>
            <form wire:submit="uploadVersion" class="mt-4 flex flex-col gap-3">
                <flux:input type="file" label="File versi baru" wire:model="file" />
                <flux:input label="Catatan Perubahan" wire:model="changeNote" />
                <div><flux:button type="submit" size="sm">Unggah Versi Baru</flux:button></div>
            </form>

            <div class="mt-4 flex flex-col gap-2">
                @foreach ($versions as $version)
                    <div wire:key="version-{{ $version->id }}" class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:text>v{{ $version->version_number }} — {{ $version->uploader->name }} — {{ $version->created_at->translatedFormat('d F Y, H:i') }}</flux:text>
                        <flux:button href="{{ route('archive.download.version', [$document, $version->version_number]) }}" size="sm">Unduh</flux:button>
                    </div>
                @endforeach
            </div>
        </flux:card>

        @if ($access_level === 'terbatas')
            <flux:card class="mt-6">
                <flux:heading size="lg">Hak Akses Khusus</flux:heading>
                <form wire:submit="grantPermission" class="mt-4 grid grid-cols-3 gap-3">
                    <flux:select label="Jenis" wire:model="granteeType">
                        @foreach ($granteeTypes as $case)
                            <option value="{{ $case->value }}">{{ $case->value }}</option>
                        @endforeach
                    </flux:select>
                    <flux:select label="Pengguna (jika jenis = user)" wire:model="granteeId">
                        <option value="">—</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </flux:select>
                    <div class="flex items-end"><flux:button type="submit" size="sm">Tambah Akses</flux:button></div>
                </form>

                <div class="mt-4 flex flex-col gap-2">
                    @foreach ($permissions as $permission)
                        <div wire:key="permission-{{ $permission->id }}" class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <flux:text>{{ $permission->grantee_type->value }} #{{ $permission->grantee_id }}</flux:text>
                            <flux:button wire:click="revokePermission({{ $permission->id }})" size="sm" variant="danger">Cabut</flux:button>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endif
    @endif
</div>
