<?php

namespace App\Enums;

enum ExpertiseLevel: string
{
    case Pemula = 'pemula';
    case Menengah = 'menengah';
    case Pakar = 'pakar';

    public function label(): string
    {
        return match ($this) {
            self::Pemula => 'Pemula',
            self::Menengah => 'Menengah',
            self::Pakar => 'Pakar',
        };
    }
}
