<?php

namespace App\Enums;

enum PostType: string
{
    case Berita = 'berita';
    case Artikel = 'artikel';
    case Opini = 'opini';
    case PressRelease = 'press_release';

    public function label(): string
    {
        return match ($this) {
            self::Berita => 'Berita',
            self::Artikel => 'Artikel',
            self::Opini => 'Opini',
            self::PressRelease => 'Siaran Pers',
        };
    }
}
