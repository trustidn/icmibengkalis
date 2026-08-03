<?php

namespace App\Services\Content;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class PartnerService
{
    /** Partner aktif terurut — dipakai strip beranda (kosong = section disembunyikan). */
    public function active(): Collection
    {
        return Cache::remember('public.partners.active', now()->addMinutes(10), function () {
            return Partner::query()->active()->with('media')->get();
        });
    }

    public function create(array $data, ?UploadedFile $logo = null): Partner
    {
        $partner = Partner::create($data);

        if ($logo) {
            $partner->addMedia($logo->getRealPath())
                ->usingFileName('logo-'.$partner->id.'.'.$logo->getClientOriginalExtension())
                ->toMediaCollection('logo');
        }

        $this->flushCache();

        return $partner;
    }

    /** Logo hanya diganti bila berkas baru diunggah; bila null, logo lama dipertahankan. */
    public function update(Partner $partner, array $data, ?UploadedFile $logo = null): Partner
    {
        $partner->update($data);

        if ($logo) {
            $partner->addMedia($logo->getRealPath())
                ->usingFileName('logo-'.$partner->id.'.'.$logo->getClientOriginalExtension())
                ->toMediaCollection('logo');
        }

        $this->flushCache();

        return $partner->fresh();
    }

    public function toggleActive(Partner $partner): void
    {
        $partner->update(['is_active' => ! $partner->is_active]);
        $this->flushCache();
    }

    public function delete(Partner $partner): void
    {
        $partner->delete();
        $this->flushCache();
    }

    private function flushCache(): void
    {
        Cache::forget('public.partners.active');
    }
}
