<div class="p-6">
    <flux:heading size="xl">Periode Kepengurusan</flux:heading>

    <flux:card class="mt-6 max-w-xl">
        <form wire:submit="create" class="flex flex-col gap-3">
            <flux:input label="Nama Periode" wire:model="name" placeholder="cth: 2025-2030" />
            <div class="grid grid-cols-2 gap-3">
                <flux:input type="date" label="Mulai" wire:model="starts_at" />
                <flux:input type="date" label="Berakhir" wire:model="ends_at" />
            </div>
            <div><flux:button type="submit" variant="primary">Tambah Periode</flux:button></div>
        </form>
    </flux:card>

    @if ($periods->count() > 1)
        <flux:card class="mt-6 max-w-xl">
            <flux:heading size="lg">Salin Struktur ke Periode Baru</flux:heading>
            <form wire:submit.prevent="" class="mt-3 grid grid-cols-3 items-end gap-3" x-data="{ from: '', to: '' }">
                <flux:select label="Dari Periode" x-model="from">
                    <option value="">—</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                    @endforeach
                </flux:select>
                <flux:select label="Ke Periode" x-model="to">
                    <option value="">—</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                    @endforeach
                </flux:select>
                <flux:button size="sm" x-on:click="$wire.copyStructure(from, to)">Salin Struktur</flux:button>
            </form>
        </flux:card>
    @endif

    <div class="mt-6 flex flex-col gap-3">
        @foreach ($periods as $period)
            <div wire:key="period-{{ $period->id }}" class="flex items-center justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div>
                    <flux:text class="font-semibold">{{ $period->name }}</flux:text>
                    <flux:text size="sm" class="text-zinc-500">{{ $period->starts_at->format('d/m/Y') }} — {{ $period->ends_at->format('d/m/Y') }}</flux:text>
                    @if ($period->is_active)
                        <flux:badge size="sm" class="ml-2">Aktif</flux:badge>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button :href="route('admin.organization.units', $period)" size="sm" wire:navigate>Kelola Struktur</flux:button>
                    @unless ($period->is_active)
                        <flux:button wire:click="activate({{ $period->id }})" size="sm" variant="primary">Jadikan Aktif</flux:button>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</div>
