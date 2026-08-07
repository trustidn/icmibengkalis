<?php

namespace App\Services\Membership;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use App\Support\NiaGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

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
            ->with(['district', 'user.roles'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('nia', 'like', "%{$search}%"));
            })
            ->when($filters['district_id'] ?? null, fn ($query, $value) => $query->where('district_id', $value))
            ->when($filters['profession'] ?? null, fn ($query, $value) => $query->where('profession', 'like', "%{$value}%"))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['education_level'] ?? null, function ($query, $value) {
                $query->whereHas('educations', fn ($q) => $q->where('level', $value));
            })
            ->latest('created_at')
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
     * periode aktif tampil dahulu sesuai urutan hierarki jabatan, lalu tanggal
     * lahir (yang punya tampil dahulu, tertua lebih dulu), lalu nama (abjad).
     *
     * @return Collection<int, Member>
     */
    public function orderedForOrgChart(): Collection
    {
        $rank = $this->positionRankByMemberId();

        return Member::query()
            ->with('district')
            ->get()
            ->sortBy(fn (Member $member) => [
                $rank[$member->id] ?? PHP_INT_MAX,
                $member->birth_date?->timestamp ?? PHP_INT_MAX,
                mb_strtolower($member->full_name),
            ])
            ->values();
    }

    /** @return array<int, int> peta member_id => urutan hierarki jabatan pada periode aktif */
    private function positionRankByMemberId(): array
    {
        return $this->positionOrderingMaps()['rank'];
    }

    /**
     * Peta urutan & level hierarki jabatan pada periode aktif, ditelusuri
     * PER LEVEL (breadth-first): SEMUA pengurus level 1 dahulu, lalu seluruh
     * level 2, dan seterusnya — bukan menyelam per cabang unit.
     *
     * @return array{rank: array<int, int>, level: array<int, int>}
     */
    private function positionOrderingMaps(): array
    {
        $activePeriod = OrgPeriod::where('is_active', true)->first();

        if (! $activePeriod) {
            return ['rank' => [], 'level' => []];
        }

        $unitsByParent = OrgUnit::where('org_period_id', $activePeriod->id)
            ->with('assignments')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(fn (OrgUnit $unit) => $unit->parent_id ?? 0);

        $rank = [];
        $level = [];
        $queue = $unitsByParent->get(0, collect());
        $depth = 1;

        while ($queue->isNotEmpty()) {
            $next = collect();

            foreach ($queue as $unit) {
                foreach ($unit->assignments as $assignment) {
                    // Penugasan eksternal (tanpa member) tidak ikut peringkat daftar anggota.
                    if ($assignment->member_id !== null && ! isset($rank[$assignment->member_id])) {
                        $rank[$assignment->member_id] = count($rank);
                        $level[$assignment->member_id] = $depth;
                    }
                }

                $next = $next->merge($unitsByParent->get($unit->id, collect()));
            }

            $queue = $next;
            $depth++;
        }

        return ['rank' => $rank, 'level' => $level];
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
            ->with(['district', 'educations', 'orgAssignments.unit', 'media', 'links'])
            ->when(
                ctype_digit($identifier),
                fn ($query) => $query->where('id', $identifier),
                fn ($query) => $query->where('slug', $identifier)
            )
            ->first();
    }
}
