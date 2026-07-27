<?php

namespace App\Services\Agenda;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class AgendaService
{
    public function upcoming(int $perPage = 10): LengthAwarePaginator
    {
        return Event::query()
            ->where('is_published', true)
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Event
    {
        return Cache::remember("public.event.{$slug}", now()->addMinutes(10), function () use ($slug) {
            return Event::where('slug', $slug)->where('is_published', true)->first();
        });
    }

    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        return Event::query()->orderByDesc('starts_at')->paginate($perPage);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        Cache::forget("public.event.{$event->slug}");

        return $event;
    }

    public function delete(Event $event): void
    {
        Cache::forget("public.event.{$event->slug}");
        $event->delete();
    }
}
