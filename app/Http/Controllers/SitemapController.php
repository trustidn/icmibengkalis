<?php

namespace App\Http\Controllers;

use App\Enums\DocumentAccessLevel;
use App\Enums\PostStatus;
use App\Models\Album;
use App\Models\Document;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('public.sitemap.xml', now()->addHour(), function () {
            $urls = collect([
                ['loc' => route('home'), 'lastmod' => now()],
                ['loc' => route('org-chart.show'), 'lastmod' => now()],
                ['loc' => route('archive.index'), 'lastmod' => now()],
            ]);

            $urls = $urls->merge(
                Page::all()->map(fn (Page $page) => [
                    'loc' => url($page->slug),
                    'lastmod' => $page->updated_at,
                ])
            );

            $urls = $urls->merge(
                Post::where('status', PostStatus::Published)->get()->map(fn (Post $post) => [
                    'loc' => route('posts.show', $post->slug),
                    'lastmod' => $post->updated_at,
                ])
            );

            $urls = $urls->merge(
                Event::where('is_published', true)->get()->map(fn (Event $event) => [
                    'loc' => route('agenda.show', $event->slug),
                    'lastmod' => $event->updated_at,
                ])
            );

            $urls = $urls->merge(
                Album::where('is_published', true)->get()->map(fn (Album $album) => [
                    'loc' => route('gallery.show', $album->slug),
                    'lastmod' => $album->updated_at,
                ])
            );

            $urls = $urls->merge(
                Document::where('access_level', DocumentAccessLevel::Publik)->get()->map(fn (Document $document) => [
                    'loc' => route('archive.show', $document->slug),
                    'lastmod' => $document->updated_at,
                ])
            );

            return view('sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
