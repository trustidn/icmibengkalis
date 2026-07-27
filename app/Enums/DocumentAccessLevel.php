<?php

namespace App\Enums;

enum DocumentAccessLevel: string
{
    case Publik = 'publik';
    case Anggota = 'anggota';
    case Pengurus = 'pengurus';
    case Terbatas = 'terbatas';

    public function label(): string
    {
        return match ($this) {
            self::Publik => 'Publik',
            self::Anggota => 'Anggota',
            self::Pengurus => 'Pengurus',
            self::Terbatas => 'Terbatas',
        };
    }
}
