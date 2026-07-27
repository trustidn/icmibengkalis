<?php

namespace App\Livewire\Admin\Announcements;

use App\Models\Announcement;
use App\Services\Content\AnnouncementService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $body = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public bool $is_pinned = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Announcement::class);
    }

    public function create(): void
    {
        $this->authorize('create', Announcement::class);
        $this->reset(['editingId', 'title', 'body', 'starts_at', 'ends_at', 'is_pinned']);
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->authorize('update', $announcement);

        $this->editingId = $announcement->id;
        $this->title = $announcement->title;
        $this->body = $announcement->body;
        $this->starts_at = $announcement->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->ends_at = $announcement->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->is_pinned = $announcement->is_pinned;
        $this->showForm = true;
    }

    public function save(AnnouncementService $announcements): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_pinned' => ['boolean'],
        ]);

        if ($this->editingId) {
            $announcement = Announcement::findOrFail($this->editingId);
            $this->authorize('update', $announcement);
            $announcements->update($announcement, $validated);
        } else {
            $this->authorize('create', Announcement::class);
            $announcements->create([...$validated, 'created_by' => auth()->id()]);
        }

        $this->showForm = false;
    }

    public function delete(int $id, AnnouncementService $announcements): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->authorize('delete', $announcement);

        $announcements->delete($announcement);
    }

    public function render(AnnouncementService $announcements)
    {
        return view('livewire.admin.announcements.index', [
            'announcements' => $announcements->paginate(),
        ]);
    }
}
