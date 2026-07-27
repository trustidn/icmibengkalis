<?php

namespace App\Services\Content;

use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PageService
{
    public function findBySlug(string $slug): ?Page
    {
        return Cache::remember("public.page.{$slug}", now()->addMinutes(10), function () use ($slug) {
            return Page::where('slug', $slug)->first();
        });
    }

    public function all()
    {
        return Page::orderBy('title')->get();
    }

    public function update(Page $page, array $data, User $user): Page
    {
        $page->fill($data);
        $page->updated_by = $user->id;
        $page->save();

        Cache::forget("public.page.{$page->slug}");

        return $page;
    }
}
