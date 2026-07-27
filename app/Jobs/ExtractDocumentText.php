<?php

namespace App\Jobs;

use App\Models\DocumentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Smalot\PdfParser\Parser;

class ExtractDocumentText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public DocumentVersion $version) {}

    public function handle(): void
    {
        $media = $this->version->getFileMedia();

        if (! $media || $media->mime_type !== 'application/pdf') {
            return;
        }

        try {
            $text = (new Parser)->parseFile($media->getPath())->getText();
        } catch (\Throwable) {
            return;
        }

        $document = $this->version->document;
        $document->update(['extracted_text' => trim($text)]);
        $document->searchable();
    }
}
