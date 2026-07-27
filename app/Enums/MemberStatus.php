<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Aktif = 'aktif';
    case TidakAktif = 'tidak_aktif';
    case Alumni = 'alumni';
    case Meninggal = 'meninggal';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::TidakAktif => 'Tidak Aktif',
            self::Alumni => 'Alumni',
            self::Meninggal => 'Meninggal',
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::Aktif => in_array($to, [self::TidakAktif, self::Alumni, self::Meninggal], true),
            self::TidakAktif => in_array($to, [self::Aktif, self::Alumni, self::Meninggal], true),
            self::Alumni => in_array($to, [self::Aktif, self::Meninggal], true),
            self::Meninggal => false,
        };
    }
}
