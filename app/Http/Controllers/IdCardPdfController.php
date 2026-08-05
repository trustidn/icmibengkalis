<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Models\IdCardEvent;
use App\Models\Member;
use App\Services\IdCard\IdCardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class IdCardPdfController extends Controller
{
    /** 54 x 85,6 mm dalam point (1 mm = 2,8346 pt). */
    private const CARD_PAPER = [0, 0, 153.07, 242.65];

    /** Kartu milik anggota yang sedang login — otomatis tersedia untuk kegiatan yang dibuka. */
    public function own(IdCardEvent $event, IdCardService $idCard): Response
    {
        $member = auth()->user()->member;
        abort_unless($member, 403, 'Hanya anggota terdaftar yang memiliki ID card.');
        abort_unless($event->is_active, 404);

        $pdf = Pdf::loadView('idcard.pdf-single', [
            'card' => $idCard->cardData($event, $member),
        ])->setPaper(self::CARD_PAPER);

        return $pdf->download('idcard-'.Str::slug($event->name).'-'.$member->slug.'.pdf');
    }

    /** Cetak massal seluruh anggota aktif — satu PDF A4, 9 kartu per halaman. */
    public function all(IdCardEvent $event, IdCardService $idCard): Response
    {
        abort_unless(auth()->user()->can('idcard.manage'), 403);

        $members = Member::where('status', MemberStatus::Aktif)
            ->with('media')
            ->orderBy('full_name')
            ->get();
        abort_if($members->isEmpty(), 404, 'Belum ada anggota aktif.');

        $cards = $members->map(fn (Member $member) => $idCard->cardData($event, $member));

        $pdf = Pdf::loadView('idcard.pdf-massal', [
            'event' => $event,
            'cards' => $cards,
        ])->setPaper('a4');

        return $pdf->download('idcard-semua-'.Str::slug($event->name).'.pdf');
    }
}
