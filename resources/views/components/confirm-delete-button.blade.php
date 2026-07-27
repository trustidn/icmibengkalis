@props([
    'wireClick',
    'message',
    'name',
    'label' => 'Hapus',
    'heading' => 'Konfirmasi Hapus',
])

{{--
    Pengganti wire:confirm (dialog native browser): wire:confirm bisa disupres
    permanen oleh browser ("Prevent this page from creating additional dialogs")
    atau di konteks embedded/webview, membuat tombol hapus tampak tidak berfungsi
    tanpa pesan error apa pun. Modal in-app ini tidak bergantung pada dialog native.
--}}
<flux:modal.trigger name="{{ $name }}">
    <flux:button size="sm" variant="danger" {{ $attributes }}>{{ $label }}</flux:button>
</flux:modal.trigger>

<flux:modal name="{{ $name }}" class="max-w-md">
    <div class="flex flex-col gap-4">
        <flux:heading size="lg">{{ $heading }}</flux:heading>
        <flux:text>{{ $message }}</flux:text>
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="filled">Batal</flux:button>
            </flux:modal.close>
            <flux:button wire:click="{{ $wireClick }}" variant="danger">{{ $label }}</flux:button>
        </div>
    </div>
</flux:modal>
