<?php

namespace App\Services\IdCard;

use App\Models\IdCardEvent;
use App\Models\Member;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

class IdCardService
{
    /** Rasio kotak foto pada kartu (lebar : tinggi) — selaras style kartu. */
    private const FOTO_RATIO = 25 / 30;

    /**
     * QR berisi tautan ke profil publik anggota — bisa dipindai siapa pun
     * untuk memverifikasi identitas pemegang kartu. Latar QR transparan,
     * modul hijau tua agar menyatu dengan desain latar kegiatan.
     */
    public function qrDataUri(Member $member): string
    {
        $writer = new Writer(new GDLibRenderer(300));
        $png = $writer->writeString(route('profiles.show', $member->slug));

        $im = imagecreatefromstring($png);
        imagetruecolortopalette($im, false, 2);
        $putih = imagecolorclosest($im, 255, 255, 255);
        $hitam = imagecolorclosest($im, 0, 0, 0);
        imagecolorset($im, $hitam, 54, 78, 0); // hijau tua #364E00
        imagecolortransparent($im, $putih);

        ob_start();
        imagepng($im);
        $png = (string) ob_get_clean();
        imagedestroy($im);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /**
     * Data satu kartu untuk view (preview HTML maupun PDF).
     * Semua gambar dibawa sebagai data URI agar dompdf tidak bergantung
     * pada akses berkas/URL eksternal.
     */
    public function cardData(IdCardEvent $event, Member $member): array
    {
        return [
            'nama' => trim(collect([$member->title_prefix, $member->full_name])->filter()->implode(' ')
                .($member->title_suffix ? ', '.$member->title_suffix : '')),
            'nia' => $member->nia,
            'profesi' => $member->profession,
            'foto' => $this->croppedPhotoDataUri($member->getFirstMedia('photo')?->getPath()),
            'bg' => $this->fileDataUri($event->backgroundPath()),
            'qr' => $this->qrDataUri($member),
            'acara' => $event->name,
            'tanggal' => $event->event_date?->translatedFormat('d F Y'),
        ];
    }

    /** Crop foto ke rasio kotak foto kartu agar tidak gepeng saat direntang dompdf. */
    private function croppedPhotoDataUri(?string $path): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }

        $src = @imagecreatefromstring((string) file_get_contents($path));
        if ($src === false) {
            return $this->fileDataUri($path);
        }

        $w = imagesx($src);
        $h = imagesy($src);

        if ($w / $h > self::FOTO_RATIO) {
            // Sumber melebar: crop horizontal, tetap di tengah.
            $cw = (int) round($h * self::FOTO_RATIO);
            $ch = $h;
            $x = (int) (($w - $cw) / 2);
            $y = 0;
        } else {
            // Sumber potret: wajah umumnya di sepertiga atas foto — jangkar
            // crop 15% dari atas, bukan tengah, agar wajah tidak terpotong.
            $cw = $w;
            $ch = (int) round($w / self::FOTO_RATIO);
            $x = 0;
            $y = (int) (($h - $ch) * 0.15);
        }

        $dst = imagecreatetruecolor($cw, $ch);
        imagecopy($dst, $src, 0, 0, $x, $y, $cw, $ch);

        ob_start();
        imagejpeg($dst, null, 88);
        $jpeg = (string) ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }

    private function fileDataUri(?string $path): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return "data:{$mime};base64,".base64_encode((string) file_get_contents($path));
    }
}
