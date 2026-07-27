<?php

namespace App\Support;

class VideoUrl
{
    /**
     * Kenali URL YouTube/Vimeo dan turunkan provider, ID, URL embed, dan
     * (untuk YouTube) thumbnail — tanpa perlu panggilan HTTP apa pun.
     * Thumbnail Vimeo diambil terpisah via oEmbed (butuh HTTP, lihat
     * GalleryService) karena Vimeo tidak punya pola URL thumbnail publik.
     *
     * @return array{provider: string, id: string, embed_url: string, thumbnail_url: ?string}|null
     */
    public static function parse(string $url): ?array
    {
        $url = trim($url);

        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})#', $url, $matches)) {
            $id = $matches[1];

            return [
                'provider' => 'youtube',
                'id' => $id,
                'embed_url' => "https://www.youtube-nocookie.com/embed/{$id}",
                'thumbnail_url' => "https://img.youtube.com/vi/{$id}/hqdefault.jpg",
            ];
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $matches)) {
            $id = $matches[1];

            return [
                'provider' => 'vimeo',
                'id' => $id,
                'embed_url' => "https://player.vimeo.com/video/{$id}",
                'thumbnail_url' => null,
            ];
        }

        return null;
    }
}
