<?php

namespace App\Models\Concerns;

trait ResolvesMediaConversions
{
    /**
     * URL konversi media; jatuh ke file asli selama konversi belum tersedia
     * (media lama yang belum diregenerasi, atau masih diproses queue).
     */
    protected function conversionUrl(string $collection, string $conversion): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion) ? $media->getUrl($conversion) : $media->getUrl();
    }
}
