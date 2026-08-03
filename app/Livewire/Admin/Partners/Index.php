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

    /** Bila terisi, form dalam mode ubah (bukan tambah). */
    public ?int $editingId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);
    }

    public function edit(int $partnerId): void
    {
        abort_unless(auth()->user()->can('pages.manage'), 403);

        $partner = Partner::findOrFail($partnerId);

        $this->editingId = $partner->id;
        $this->name = $partner->name;
        $this->url = $partner->url;
        $this->sort_order = $partner->sort_order;
        $this->logo = null;
        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'name', 'url', 'logo', 'sort_order']);
        $this->resetValidation();
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

        $data = [
            'name' => $validated['name'],
            'url' => $validated['url'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ];

        if ($this->editingId) {
            $partners->update(Partner::findOrFail($this->editingId), $data, $this->logo);
            $pesan = 'Perubahan partner tersimpan.';
        } else {
            $partners->create([...$data, 'is_active' => true], $this->logo);
            $pesan = 'Partner tersimpan.';
        }

        $this->reset(['editingId', 'name', 'url', 'logo', 'sort_order']);

        session()->flash('partners.saved', $pesan);
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
