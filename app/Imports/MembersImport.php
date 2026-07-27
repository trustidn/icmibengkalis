<?php

namespace App\Imports;

use App\Enums\MemberStatus;
use App\Models\District;
use App\Models\Member;
use App\Support\NiaGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MembersImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 heading row, +1 to make it 1-indexed

            $data = [
                'full_name' => trim((string) ($row['nama_lengkap'] ?? '')),
                'gender' => $this->normalizeGender($row['jenis_kelamin'] ?? null),
                'birth_place' => trim((string) ($row['tempat_lahir'] ?? '')) ?: null,
                'birth_date' => $this->parseDate($row['tanggal_lahir'] ?? null),
                'address' => trim((string) ($row['alamat'] ?? '')) ?: null,
                'institution' => trim((string) ($row['instansi'] ?? '')) ?: null,
                'profession' => trim((string) ($row['profesi'] ?? '')) ?: null,
                'joined_at' => $this->parseDate($row['tanggal_bergabung'] ?? null) ?? now()->toDateString(),
            ];

            $validator = Validator::make($data, [
                'full_name' => ['required', 'string', 'max:255'],
                'gender' => ['nullable', 'in:L,P'],
                'birth_date' => ['nullable', 'date'],
                'joined_at' => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris {$rowNumber}: ".$validator->errors()->first();

                continue;
            }

            $districtName = trim((string) ($row['kecamatan'] ?? ''));
            $district = $districtName !== '' ? District::where('name', $districtName)->first() : null;

            if ($districtName !== '' && ! $district) {
                $this->errors[] = "Baris {$rowNumber}: kecamatan \"{$districtName}\" tidak ditemukan.";

                continue;
            }

            Member::create([
                ...$data,
                'nia' => NiaGenerator::generate(),
                'district_id' => $district?->id,
                'status' => MemberStatus::Aktif,
            ]);

            $this->imported++;
        }
    }

    private function normalizeGender(mixed $value): ?string
    {
        $value = strtoupper(trim((string) $value));

        return in_array($value, ['L', 'P'], true) ? $value : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
