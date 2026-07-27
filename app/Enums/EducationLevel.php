<?php

namespace App\Enums;

enum EducationLevel: string
{
    case S1 = 'S1';
    case S2 = 'S2';
    case S3 = 'S3';
    case D3 = 'D3';
    case SMA = 'SMA';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Lainnya => 'Lainnya',
            default => $this->value,
        };
    }
}
