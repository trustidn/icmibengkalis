<?php

namespace App\Services\Membership;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use App\Support\NiaGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class MemberService
{
    /** Field yang dinilai untuk indikator kelengkapan profil anggota. */
    private const COMPLETION_CHECKS = [
        'gender', 'birth_place', 'birth_date', 'address', 'district_id',
        'institution', 'profession', 'expertise', 'bio',
    ];

    public function create(array $data): Member
    {
        $data['nia'] = $data['nia'] ?? NiaGenerator::generate();
        $data['status'] = $data['status'] ?? MemberStatus::Aktif;

        return Member::create($data);
    }

    public function update(Member $member, array $data): Member
    {
        $member->update($data);

        return $member;
    }

    public function delete(Member $member): void
    {
        $member->delete();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Member::query()
            ->with(['district'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('nia', 'like', "%{$search}%"));
            })
            ->when($filters['district_id'] ?? null, fn ($query, $value) => $query->where('district_id', $value))
            ->when($filters['profession'] ?? null, fn ($query, $value) => $query->where('profession', 'like', "%{$value}%"))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['education_level'] ?? null, function ($query, $value) {
                $query->whereHas('educations', fn ($q) => $q->where('level', $value));
            })
            ->orderBy('full_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** Persentase kelengkapan profil (0-100), dipakai dashboard & form profil anggota. */
    public function profileCompletionPercentage(Member $member): int
    {
        $total = count(self::COMPLETION_CHECKS) + 2; // +foto, +minimal 1 riwayat pendidikan
        $filled = 0;

        foreach (self::COMPLETION_CHECKS as $field) {
            if (filled($member->{$field})) {
                $filled++;
            }
        }

        if ($member->photoUrl()) {
            $filled++;
        }

        if ($member->educations()->exists()) {
            $filled++;
        }

        return (int) round(($filled / $total) * 100);
    }

    /**
     * Daftar seluruh anggota untuk halaman Struktur Organisasi publik: pengurus
     * periode aktif tampil dahulu sesuai urutan hierarki jabatan, sisanya
     * (bukan pengurus) menyusul terurut tanggal bergabung.
     */
    public function paginateOrderedByPositionThenJoined(int $page, int $perPage = 20): LengthAwarePaginator
    {
        $rank = $this->positionRankByMemberId();

        $members = Member::query()
            ->with('district')
            ->get()
            ->sortBy(fn (Member $member) => [
                $rank[$member->id] ?? PHP_INT_MAX,
                $member->joined_at?->timestamp ?? PHP_INT_MAX,
            ])
            ->values();

        return new Paginator(
            $members->forPage($page, $perPage),
            $members->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }

    /** @return array<int, int> peta member_id => urutan hierarki jabatan pada periode aktif */
    private function positionRankByMemberId(): array
    {
        $activePeriod = OrgPeriod::where('is_active', true)->first();

        if (! $activePeriod) {
            return [];
        }

        $rootUnits = OrgUnit::where('org_period_id', $activePeriod->id)
            ->whereNull('parent_id')
            ->with('children.children.children.assignments', 'assignments')
            ->orderBy('sort_order')
            ->get();

        $rank = [];
        $this->walkUnitsForRanking($rootUnits, $rank);

        return $rank;
    }

    /** @param  EloquentCollection<int, OrgUnit>  $units
     * @param  array<int, int>  $rank */
    private function walkUnitsForRanking(EloquentCollection $units, array &$rank): void
    {
        foreach ($units as $unit) {
            foreach ($unit->assignments as $assignment) {
                // Penugasan eksternal (tanpa member) tidak ikut peringkat daftar anggota.
                if ($assignment->member_id !== null) {
                    $rank[$assignment->member_id] ??= count($rank);
                }
            }

            $this->walkUnitsForRanking($unit->children, $rank);
        }
    }

    /** Anggota Aktif dengan foto profil, diacak — dipakai carousel beranda. */
    public function randomFeatured(int $limit = 5): EloquentCollection
    {
        return Member::query()
            ->where('status', MemberStatus::Aktif)
            ->whereHas('media', fn ($q) => $q->where('collection_name', 'photo'))
            ->with('media')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /** Resolusi identifier publik: slug (dari nama) atau ID numerik. Hanya anggota Aktif yang tampil. */
    public function findPublicProfile(string $identifier): ?Member
    {
        return Member::query()
            ->where('status', MemberStatus::Aktif)
            ->with(['district', 'educations', 'orgAssignments.unit', 'media'])
            ->when(
                ctype_digit($identifier),
                fn ($query) => $query->where('id', $identifier),
                fn ($query) => $query->where('slug', $identifier)
            )
            ->first();
    }
}
