@props(['member'])

{{--
    Bungkus nama/avatar anggota agar bisa diklik menuju profil publiknya.
    Hanya jadi tautan bila anggota berstatus Aktif (profil publik aktif);
    selain itu tampil sebagai teks biasa agar tidak mengarah ke halaman 404.
--}}
@if ($member && $member->status === \App\Enums\MemberStatus::Aktif)
    <a href="{{ route('profiles.show', $member->slug ?? $member->id) }}" wire:navigate {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <span {{ $attributes }}>{{ $slot }}</span>
@endif
