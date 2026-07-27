<?php

namespace App\Enums;

enum ContactMessageStatus: string
{
    case Baru = 'baru';
    case Dibaca = 'dibaca';
    case Dibalas = 'dibalas';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Dibaca => 'Dibaca',
            self::Dibalas => 'Dibalas',
        };
    }
}
