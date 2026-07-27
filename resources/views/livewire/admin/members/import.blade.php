<div class="mx-auto max-w-2xl p-6">
    <flux:heading size="xl">Impor Anggota dari Excel</flux:heading>
    <flux:text class="mt-2 text-zinc-500">
        Kolom yang dikenali: nama_lengkap, jenis_kelamin (L/P), tempat_lahir, tanggal_lahir, alamat,
        kecamatan (harus sama persis dengan nama kecamatan di sistem), instansi, profesi, tanggal_bergabung.
    </flux:text>

    <form wire:submit="import" class="mt-6 flex flex-col gap-4">
        <flux:input type="file" label="File Excel (.xlsx/.xls/.csv)" wire:model="file" />
        <div><flux:button type="submit" variant="primary">Impor</flux:button></div>
    </form>

    @if ($imported !== null)
        <flux:callout class="mt-6" variant="success">
            {{ $imported }} anggota berhasil diimpor.
        </flux:callout>

        @if (count($errors_list) > 0)
            <flux:callout class="mt-4" variant="danger">
                <flux:heading size="sm">Baris gagal diimpor ({{ count($errors_list) }})</flux:heading>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors_list as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </flux:callout>
        @endif
    @endif
</div>
