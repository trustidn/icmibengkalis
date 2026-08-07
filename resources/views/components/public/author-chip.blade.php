@props(['user', 'dark' => false])

{{--
    Chip penulis artikel: foto profil + nama + profesi, bertaut ke profil publik
    (hanya bila anggota Aktif — via x-public.member-link). Varian dark untuk
    latar gelap (kartu utama beranda). Catatan: jangan letakkan di dalam <a>
    lain — bila kartu memakai overlay-link, beri chip posisi relative + z-index.
--}}
@php $member = $user?->member; @endphp

@if ($user)
    <x-public.member-link :member="$member" :class="'flex items-center gap-2.5 min-w-0 '.$attributes->get('class')">
        @if ($member?->photoUrl())
            <img src="{{ $member->photoUrl() }}" alt="{{ $member->full_name }}"
                 class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 {{ $dark ? 'ring-white/30' : 'ring-outline-variant/30' }}" />
        @else
            <span class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center {{ $dark ? 'bg-white/15 text-white/80' : 'bg-primary-container/15 text-primary' }}">
                <span class="material-symbols-outlined text-[18px]">person</span>
            </span>
        @endif
        <span class="min-w-0 text-left">
            <span class="block font-bold text-sm leading-tight truncate {{ $dark ? 'text-white' : 'text-on-surface' }}">{{ $member?->fullNameWithTitles() ?? $user->name }}</span>
            @if ($member?->profession)
                <span class="block text-xs leading-tight truncate {{ $dark ? 'text-white/60' : 'text-on-surface-variant' }}">{{ $member->profession }}</span>
            @endif
        </span>
    </x-public.member-link>
@endif
