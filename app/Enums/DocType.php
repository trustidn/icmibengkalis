<?php

namespace App\Enums;

enum DocType: string
{
    case Surat = 'surat';
    case Notulen = 'notulen';
    case Rapat = 'rapat';
    case Sk = 'sk';
    case Sop = 'sop';
    case Foto = 'foto';
    case Video = 'video';
    case Seminar = 'seminar';
    case Buku = 'buku';
    case Artikel = 'artikel';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Surat => 'Surat',
            self::Notulen => 'Notulen',
            self::Rapat => 'Rapat',
            self::Sk => 'SK',
            self::Sop => 'SOP',
            self::Foto => 'Foto',
            self::Video => 'Video',
            self::Seminar => 'Seminar',
            self::Buku => 'Buku',
            self::Artikel => 'Artikel',
            self::Lainnya => 'Lainnya',
        };
    }
}
