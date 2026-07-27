<div class="p-6">
    <flux:heading size="xl">Pesan Kontak</flux:heading>

    <div class="mt-6 flex flex-col gap-4">
        @foreach ($messages as $message)
            <flux:card wire:key="message-{{ $message->id }}">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading>{{ $message->subject }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">{{ $message->name }} &lt;{{ $message->email }}&gt;</flux:text>
                    </div>
                    <flux:badge size="sm">{{ $message->status->label() }}</flux:badge>
                </div>
                <flux:text class="mt-3">{{ $message->message }}</flux:text>

                <div class="mt-3 flex gap-2">
                    <flux:button wire:click="markRead({{ $message->id }})" size="sm">Tandai Dibaca</flux:button>
                    <flux:button wire:click="markReplied({{ $message->id }})" size="sm" variant="primary">Tandai Dibalas</flux:button>
                </div>
            </flux:card>
        @endforeach
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
</div>
