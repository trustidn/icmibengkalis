<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">ID Card Kegiatan</flux:heading>
    <flux:text class="mt-1 text-zinc-500">
        ID card Anda otomatis tersedia untuk setiap kegiatan yang dibuka — tanpa perlu mendaftar.
        Nama dan foto diambil dari profil keanggotaan Anda; pastikan profil Anda lengkap.
    </flux:text>

    @include('idcard.style')

    <div class="mt-6 flex flex-col gap-4">
        @forelse ($events as $event)
            <flux:card wire:key="idcard-{{ $event->id }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">{{ $event->name }}</flux:heading>
                        @if ($event->event_date)
                            <flux:text class="text-sm text-zinc-500">{{ $event->event_date->translatedFormat('d F Y') }}</flux:text>
                        @endif
                    </div>
                    <flux:button :href="route('member.idcard.print', $event)" variant="primary" size="sm">Unduh PDF</flux:button>
                </div>

                <div class="mt-4 flex justify-center rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                    @include('idcard.card', $cards[$event->id])
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text class="text-zinc-500">Belum ada kegiatan ber-ID card yang dibuka. Pantau halaman ini saat ada kegiatan baru.</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
