<?php

namespace App\Services\Content;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactService
{
    public function submit(array $data): ContactMessage
    {
        return ContactMessage::create([
            ...$data,
            'status' => ContactMessageStatus::Baru,
        ]);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ContactMessage::query()->latest()->paginate($perPage);
    }

    public function markStatus(ContactMessage $message, ContactMessageStatus $status): ContactMessage
    {
        $message->update(['status' => $status]);

        return $message;
    }
}
