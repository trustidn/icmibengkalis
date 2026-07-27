<?php

namespace App\Services\Content;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class AnnouncementService
{
    public function active(): Collection
    {
        return Cache::remember('public.announcements.active', now()->addMinutes(5), function () {
            $now = now();

            return Announcement::query()
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                ->orderByDesc('is_pinned')
                ->orderByDesc('starts_at')
                ->get();
        });
    }

    public function paginate(int $perPage = 10)
    {
        return Announcement::query()->orderByDesc('is_pinned')->orderByDesc('created_at')->paginate($perPage);
    }

    public function create(array $data): Announcement
    {
        $announcement = Announcement::create($data);
        $this->flushCache();

        return $announcement;
    }

    public function update(Announcement $announcement, array $data): Announcement
    {
        $announcement->update($data);
        $this->flushCache();

        return $announcement;
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
        $this->flushCache();
    }

    private function flushCache(): void
    {
        Cache::forget('public.announcements.active');
    }
}
