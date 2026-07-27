<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::InReview => 'Menunggu Review',
            self::Published => 'Terbit',
            self::Rejected => 'Ditolak',
            self::Archived => 'Diarsipkan',
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::Draft => in_array($to, [self::InReview], true),
            self::InReview => in_array($to, [self::Published, self::Rejected, self::Draft], true),
            self::Rejected => in_array($to, [self::Draft], true),
            self::Published => in_array($to, [self::Archived, self::Draft], true),
            self::Archived => in_array($to, [self::Draft], true),
        };
    }
}
