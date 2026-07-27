<div class="mx-auto max-w-3xl p-6">
    <flux:heading size="xl">Penugasan — {{ $unit->name }}</flux:heading>

    <flux:card class="mt-6">
        <form wire:submit="addAssignment" class="flex flex-col gap-3">
            <flux:select label="Anggota" wire:model="member_id">
                <option value="">—</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->nia }})</option>
                @endforeach
            </flux:select>
            <flux:input label="Jabatan" wire:model="position_title" placeholder="cth: Ketua Bidang" />
            <flux:textarea label="Riwayat Singkat" wire:model="short_bio" rows="3" />
            <flux:checkbox wire:model="show_contact" label="Tampilkan kontak di profil publik" />
            <div><flux:button type="submit" variant="primary">Tambah Penugasan</flux:button></div>
        </form>
    </flux:card>

    <div class="mt-6 flex flex-col gap-2">
        @foreach ($assignments as $assignment)
            <div wire:key="assignment-{{ $assignment->id }}" class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text>{{ $assignment->position_title }} — {{ $assignment->member->full_name }}</flux:text>
                <x-confirm-delete-button name="confirm-delete-assignment-{{ $assignment->id }}" wire-click="deleteAssignment({{ $assignment->id }})" message="Hapus penugasan ini?" />
            </div>
        @endforeach
    </div>
</div>
