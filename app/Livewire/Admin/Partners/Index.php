<?php

namespace App\Livewire\Admin\Partners;

use App\Models\Partner;
use App\Services\Content\PartnerService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Kelola link partner/mitra beranda. Gate: pages.manage (pengelola konten situs).
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $url = '';

    public $logo = null;

    public ?int $sort_order = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);
    }

    public function save(PartnerService $partners): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:1024'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $partners->create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ], $this->logo);

        $this->reset(['name', 'url', 'logo', 'sort_order']);

        session()->flash('partners.saved', 'Partner tersimpan.');
    }

    public function toggleActive(int $partnerId, PartnerService $partners): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        $partners->toggleActive(Partner::findOrFail($partnerId));
    }

    public function deletePartner(int $partnerId, PartnerService $partners): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        $partners->delete(Partner::findOrFail($partnerId));
    }

    public function render()
    {
        return view('livewire.admin.partners.index', [
            'partners' => Partner::with('media')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}
