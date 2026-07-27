<?php

namespace App\Services\Membership;

use App\Imports\MembersImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class MemberImportService
{
    public function import(UploadedFile $file): MembersImport
    {
        $import = new MembersImport;

        Excel::import($import, $file);

        return $import;
    }
}
