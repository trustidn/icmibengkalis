<?php

namespace App\Livewire\Public\Pages;

use App\Services\Content\PageService;
use Illuminate\Support\Str;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Show extends Component
{
    public string $slug;

    public function mount(string $slug, PageService $pages): void
    {
        $page = $pages->findBySlug($slug);

        abort_unless($page, Response::HTTP_NOT_FOUND);

        $this->slug = $slug;
    }

    public function render(PageService $pages)
    {
        $page = $pages->findBySlug($this->slug);

        return view('livewire.public.pages.show', ['page' => $page])
            ->layout('components.layouts.public', [
                'metaTitle' => $page->title.' — '.config('app.name'),
                'metaDescription' => $page->seo_meta['description'] ?? Str::limit(strip_tags((string) $page->body), 160),
            ]);
    }
}
