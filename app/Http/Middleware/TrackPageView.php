<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            PageView::firstOrCreate([
                'path' => $request->path(),
                'viewed_on' => now()->toDateString(),
            ])->increment('count');
        }

        return $response;
    }
}
