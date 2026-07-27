<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class Html
{
    /**
     * Render body konten untuk tampilan publik.
     *
     * Konten lama tersimpan sebagai teks polos (pra-editor), konten baru sebagai
     * HTML dari editor — keduanya harus tampil benar dan aman dari XSS.
     */
    public static function display(?string $body): HtmlString
    {
        $body = trim((string) $body);

        if ($body === '') {
            return new HtmlString('');
        }

        if (! str_contains($body, '<')) {
            return new HtmlString(nl2br(e($body)));
        }

        return new HtmlString(clean($body));
    }

    /** Ringkasan teks polos dari body (untuk kartu/kutipan). */
    public static function excerpt(?string $body, int $words = 50): string
    {
        return str(strip_tags((string) $body))->squish()->words($words)->toString();
    }
}
