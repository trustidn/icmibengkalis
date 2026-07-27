<?php

namespace App\Listeners;

use App\Events\PostPublished;
use Illuminate\Support\Facades\Cache;

class ClearPublishedPostCache
{
    public function handle(PostPublished $event): void
    {
        Cache::forget("public.post.{$event->post->slug}");
        Cache::forget('public.post.latest');
    }
}
