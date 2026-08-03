<?php

namespace App\Http\Controllers;

use App\Services\System\DatabaseBackupService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    /** Unduh dump database ter-gzip (gate settings.manage di route). */
    public function download(DatabaseBackupService $backup): StreamedResponse
    {
        abort_unless($backup->supported(), 404);

        set_time_limit(600);

        $filename = 'backup-icmi-'.now()->format('Ymd-His').'.sql.gz';

        return response()->streamDownload(
            fn () => $backup->streamDump(),
            $filename,
            ['Content-Type' => 'application/gzip'],
        );
    }
}
