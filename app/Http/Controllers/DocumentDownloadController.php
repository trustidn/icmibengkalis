<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DocumentDownloadController extends Controller
{
    public function show(Request $request, Document $document, ?int $version = null): Response
    {
        Gate::authorize('view', $document);

        $documentVersion = $version
            ? $document->versions()->where('version_number', $version)->firstOrFail()
            : $document->latestVersion();

        abort_unless($documentVersion, 404);

        $media = $documentVersion->getFileMedia();

        abort_unless($media, 404);

        if ($request->boolean('preview')) {
            return $media->toInlineResponse($request);
        }

        return $media->toResponse($request);
    }
}
