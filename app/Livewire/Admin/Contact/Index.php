<?php

namespace App\Livewire\Admin\Contact;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use App\Services\Content\ContactService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', ContactMessage::class);
    }

    public function markRead(int $messageId, ContactService $contact): void
    {
        $message = ContactMessage::findOrFail($messageId);
        $this->authorize('update', $message);

        $contact->markStatus($message, ContactMessageStatus::Dibaca);
    }

    public function markReplied(int $messageId, ContactService $contact): void
    {
        $message = ContactMessage::findOrFail($messageId);
        $this->authorize('update', $message);

        $contact->markStatus($message, ContactMessageStatus::Dibalas);
    }

    public function render(ContactService $contact)
    {
        return view('livewire.admin.contact.index', [
            'messages' => $contact->paginate(),
        ]);
    }
}
