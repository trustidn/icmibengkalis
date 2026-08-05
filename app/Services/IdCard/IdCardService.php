<?php

namespace App\Services\IdCard;

use App\Models\IdCardEvent;
use App\Models\Member;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

class IdCardService
{
    /**
     * QR berisi tautan ke profil publik anggota — bisa dipindai siapa pun
     * untuk memverifikasi identitas pemegang kartu.
     */
    public function qrDataUri(Member $member): string
    {
        $writer = new Writer(new GDLibRenderer(300));
        $png = $writer->writeString(route('profiles.show', $member->slug));

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
            'foto' => $this->fileDataUri($member->getFirstMedia('photo')?->getPath()),
            'bg' => $this->fileDataUri($event->backgroundPath()),
            'qr' => $this->qrDataUri($member),
            'acara' => $event->name,
            'tanggal' => $event->event_date?->translatedFormat('d F Y'),
        ];
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
