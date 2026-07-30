@props(['unit', 'search' => '', 'depth' => 0])

@php
    $matchesSearch = function ($unit) use (&$matchesSearch, $search) {
        if ($search === '') {
            return true;
        }

        $needle = mb_strtolower($search);

        foreach ($unit->assignments as $assignment) {
            if (str_contains(mb_strtolower($assignment->displayName()), $needle)) {
                return true;
            }
        }

        if (str_contains(mb_strtolower($unit->name), $needle)) {
            return true;
        }

        foreach ($unit->children as $child) {
            if ($matchesSearch($child)) {
                return true;
            }
        }

        return false;
    };

    $isMatch = $matchesSearch($unit);
@endphp

<li x-data="{ open: true }" class="{{ $isMatch ? '' : 'opacity-30' }}">
    <div class="relative bg-white rounded-xl border border-outline-variant/40 border-t-4 {{ $depth === 0 ? 'border-t-primary' : 'border-t-primary-container' }} shadow-sm hover:shadow-md transition-shadow w-52 px-4 pt-3 {{ $unit->children->isNotEmpty() ? 'pb-5' : 'pb-4' }} text-center">
        <p class="font-label-lg text-label-lg font-bold uppercase tracking-wider {{ $depth === 0 ? 'text-primary' : 'text-on-surface-variant' }}">{{ $unit->name }}</p>

        <div class="mt-3 flex flex-col gap-3">
            @forelse ($unit->assignments as $assignment)
                <flux:modal.trigger name="assignment-{{ $assignment->id }}">
                    <button type="button" class="group flex flex-col items-center gap-1.5 w-full">
                        @if ($assignment->member?->photoUrl())
                            <img src="{{ $assignment->member->photoUrl() }}" alt="{{ $assignment->displayName() }}"
                                 class="w-14 h-14 rounded-full object-cover ring-2 ring-outline-variant/30 group-hover:ring-primary transition-all" />
                        @else
                            <span class="w-14 h-14 rounded-full bg-surface-container-low ring-2 ring-outline-variant/30 group-hover:ring-primary flex items-center justify-center transition-all">
                                <span class="material-symbols-outlined text-outline text-[26px]">person</span>
                            </span>
                        @endif
                        <span class="font-headline-md text-[14px] font-bold text-on-surface leading-tight group-hover:text-primary transition-colors">{{ $assignment->displayName() }}</span>
                        <span class="font-body-md text-xs text-on-surface-variant leading-tight">{{ $assignment->position_title }}</span>
                    </button>
                </flux:modal.trigger>

                <flux:modal name="assignment-{{ $assignment->id }}" class="max-w-md">
                    <div class="flex flex-col items-start gap-2">
                        @if ($assignment->member?->photoUrl())
                            <img src="{{ $assignment->member->photoUrl() }}" alt="{{ $assignment->displayName() }}" class="w-16 h-16 rounded-full object-cover mb-1" />
                        @else
                            <x-public.image-placeholder icon="person" class="w-16 h-16 rounded-full mb-1" />
                        @endif
                        @if ($assignment->member)
                            <x-public.member-link :member="$assignment->member" class="hover:text-primary transition-colors w-fit">
                                <flux:heading size="lg">{{ $assignment->member->full_name }}</flux:heading>
                            </x-public.member-link>
                        @else
                            <flux:heading size="lg">{{ $assignment->displayName() }}</flux:heading>
                        @endif
                        <flux:text size="sm" class="text-zinc-500">Masa Jabatan {{ $unit->period->name }}</flux:text>
                        <flux:text class="font-medium">{{ $assignment->position_title }} · {{ $unit->name }}</flux:text>
                        @if ($assignment->short_bio)
                            <flux:text class="mt-2">{{ $assignment->short_bio }}</flux:text>
                        @endif
                        @if ($assignment->member?->status === \App\Enums\MemberStatus::Aktif)
                            <flux:button :href="route('profiles.show', $assignment->member->slug ?? $assignment->member->id)" wire:navigate variant="primary" size="sm" class="mt-2 w-fit">
                                Lihat Profil Lengkap
                            </flux:button>
                        @endif
                    </div>
                </flux:modal>
            @empty
                <p class="font-body-md text-xs text-outline italic">Belum ada pengurus</p>
            @endforelse
        </div>

        @if ($unit->children->isNotEmpty())
            <button type="button" x-on:click="open = !open"
                    class="absolute -bottom-3 left-1/2 -translate-x-1/2 z-10 w-6 h-6 rounded-full bg-white border border-outline-variant/50 text-on-surface-variant hover:border-primary hover:text-primary transition-colors flex items-center justify-center">
                <span class="material-symbols-outlined text-[16px]" x-show="open">remove</span>
                <span class="material-symbols-outlined text-[16px]" x-show="!open" x-cloak>add</span>
            </button>
        @endif
    </div>

    @if ($unit->children->isNotEmpty())
        <ul x-show="open">
            @foreach ($unit->children as $child)
                <x-org-chart.node :unit="$child" :search="$search" :depth="$depth + 1" />
            @endforeach
        </ul>
    @endif
</li>
