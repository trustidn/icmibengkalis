<?php

namespace App\Support;

use App\Models\Member;
use Illuminate\Support\Facades\DB;

class NiaGenerator
{
    public static function generate(): string
    {
        $year = now()->year;
        $prefix = "ICMI-{$year}-";

        return DB::transaction(function () use ($prefix) {
            $lastSequence = Member::withTrashed()
                ->where('nia', 'like', $prefix.'%')
                ->lockForUpdate()
                ->get()
                ->map(fn (Member $member) => (int) substr($member->nia, -4))
                ->max() ?? 0;

            return $prefix.str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
        });
    }
}
