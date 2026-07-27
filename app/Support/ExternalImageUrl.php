<?php

namespace App\Support;

class ExternalImageUrl
{
    /**
     * Ubah tautan share umum (Google Drive, Dropbox) menjadi URL yang bisa
     * langsung diunduh sebagai berkas gambar. URL lain (Flickr, CDN Instagram/
     * Facebook yang disalin langsung, atau URL gambar biasa) dikembalikan apa
     * adanya — proses unduhannya sendiri divalidasi belakangan di GalleryService.
     */
    public static function normalize(string $url): string
    {
        $url = trim($url);

        // Google Drive: /file/d/{id}/view... atau open?id={id} -> uc?export=view&id={id}
        if (preg_match('#drive\.google\.com/file/d/([a-zA-Z0-9_-]+)#', $url, $matches)
            || preg_match('#drive\.google\.com/open\?id=([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return "https://drive.google.com/uc?export=view&id={$matches[1]}";
        }

        // Dropbox: paksa mode raw agar mengembalikan berkas, bukan halaman pratinjau.
        if (str_contains($url, 'dropbox.com')) {
            $url = preg_replace('/([?&])dl=[01]/', '$1raw=1', $url) ?? $url;

            if (! str_contains($url, 'raw=1')) {
                $url .= (str_contains($url, '?') ? '&' : '?').'raw=1';
            }
        }

        return $url;
    }
}
