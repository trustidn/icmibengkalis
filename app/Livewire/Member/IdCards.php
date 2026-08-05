<?php

namespace App\Livewire\Member;

use App\Models\IdCardEvent;
use App\Services\IdCard\IdCardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class IdCards extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->member !== null, 403, 'Hanya anggota terdaftar yang memiliki ID card.');
    }

    public function render(IdCardService $idCard)
    {
        $member = auth()->user()->member;

        // Setiap anggota otomatis memiliki kartu untuk semua kegiatan yang dibuka.
        $events = IdCardEvent::where('is_active', true)->latest()->get();

        $cards = $events->mapWithKeys(
            fn (IdCardEvent $event) => [$event->id => $idCard->cardData($event, $member)]
        );

        return view('livewire.member.id-cards', [
            'events' => $events,
            'cards' => $cards,
        ]);
    }
}
