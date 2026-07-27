<?php

namespace App\Livewire\Public\Announcements;

use App\Services\Content\AnnouncementService;
use Livewire\Component;

class Index extends Component
{
    public function render(AnnouncementService $announcements)
    {
        return view('livewire.public.announcements.index', [
            'announcements' => $announcements->active(),
        ])->layout('components.layouts.public', [
            'metaTitle' => 'Pengumuman — '.config('app.name'),
        ]);
    }
}
