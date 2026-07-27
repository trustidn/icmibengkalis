<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    @php
        $site = \App\Models\SiteSetting::current();

        $profilePages = [
            '/tentang' => __('nav.about'),
            '/sejarah' => __('nav.history'),
            '/visi-misi' => __('nav.vision_mission'),
            '/sambutan-ketua' => __('nav.chairman_greeting'),
            '/program-kerja' => __('nav.programs'),
        ];
        $onProfilePage = request()->is('tentang', 'sejarah', 'visi-misi', 'sambutan-ketua', 'program-kerja', 'organisasi/*');
    @endphp
    <body class="min-h-screen bg-background text-on-background font-body-md antialiased">
        <nav class="fixed top-0 w-full z-50 glass-nav border-b border-outline-variant/30 transition-all duration-500" x-data="{ open: false, profil: false }" x-cloak>
            <div class="flex justify-between items-center w-full px-margin-mobile md:px-margin-desktop py-3 max-w-container-max mx-auto">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center">
                    @if ($site->logoUrl())
                        <img src="{{ $site->logoUrl() }}" alt="{{ $site->site_name }}" class="max-h-14 max-w-[40vw] w-auto h-auto" />
                    @else
                        <x-app-logo-icon class="max-h-14 max-w-[40vw] w-auto h-auto text-primary" />
                    @endif
                </a>

                <div class="hidden lg:flex items-center gap-7 xl:gap-9">
                    <a href="{{ route('home') }}" wire:navigate
                       class="font-label-lg text-label-lg transition-all duration-300 {{ request()->routeIs('home') ? 'text-primary font-bold relative after:absolute after:bottom-[-22px] after:left-0 after:w-full after:h-[3px] after:bg-primary' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                        {{ __('nav.home') }}
                    </a>

                    {{-- Dropdown Profil: seluruh halaman statis --}}
                    <div class="relative" @click.outside="profil = false">
                        <button type="button" @click="profil = !profil"
                                class="flex items-center gap-1 font-label-lg text-label-lg transition-all duration-300 {{ $onProfilePage ? 'text-primary font-bold relative after:absolute after:bottom-[-22px] after:left-0 after:w-full after:h-[3px] after:bg-primary' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                            {{ __('nav.profile') }}
                            <span class="material-symbols-outlined text-[20px] transition-transform duration-300" :class="profil ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="profil" x-transition.origin.top
                             class="absolute left-0 top-full mt-4 w-60 bg-white rounded-lg border border-outline-variant/30 shadow-xl shadow-black/5 py-2 overflow-hidden"
                             style="display: none;">
                            @foreach ($profilePages as $href => $label)
                                <a href="{{ $href }}" wire:navigate @click="profil = false"
                                   class="block px-5 py-2.5 font-label-lg text-label-lg transition-colors {{ request()->is(ltrim($href, '/')) ? 'text-primary font-bold bg-primary-container/10' : 'text-on-surface-variant hover:text-primary hover:bg-primary-container/5' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                            <a href="{{ route('org-chart.show') }}" wire:navigate @click="profil = false"
                               class="block px-5 py-2.5 font-label-lg text-label-lg transition-colors border-t border-outline-variant/20 {{ request()->is('organisasi/*') ? 'text-primary font-bold bg-primary-container/10' : 'text-on-surface-variant hover:text-primary hover:bg-primary-container/5' }}">
                                {{ __('nav.org_structure') }}
                            </a>
                        </div>
                    </div>

                    <a href="{{ route('posts.index') }}" wire:navigate
                       class="font-label-lg text-label-lg transition-all duration-300 {{ request()->is('berita*') ? 'text-primary font-bold relative after:absolute after:bottom-[-22px] after:left-0 after:w-full after:h-[3px] after:bg-primary' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                        {{ __('nav.news') }}
                    </a>
                    <a href="{{ route('agenda.index') }}" wire:navigate
                       class="font-label-lg text-label-lg transition-all duration-300 {{ request()->is('agenda*') ? 'text-primary font-bold relative after:absolute after:bottom-[-22px] after:left-0 after:w-full after:h-[3px] after:bg-primary' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                        {{ __('nav.agenda') }}
                    </a>
                    <a href="{{ route('gallery.index') }}" wire:navigate
                       class="font-label-lg text-label-lg transition-all duration-300 {{ request()->is('galeri*') ? 'text-primary font-bold relative after:absolute after:bottom-[-22px] after:left-0 after:w-full after:h-[3px] after:bg-primary' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                        {{ __('nav.gallery') }}
                    </a>
                    <a href="{{ route('contact.show') }}" wire:navigate
                       class="font-label-lg text-label-lg transition-all duration-300 {{ request()->is('kontak') ? 'text-primary font-bold relative after:absolute after:bottom-[-22px] after:left-0 after:w-full after:h-[3px] after:bg-primary' : 'text-on-surface-variant font-medium hover:text-primary' }}">
                        {{ __('nav.contact') }}
                    </a>
                </div>

                <div class="flex items-center gap-3 md:gap-5">
                    @auth
                        @php
                            $authUser = auth()->user();
                            $authMember = $authUser->member;
                            $authMemberActive = $authMember?->status === \App\Enums\MemberStatus::Aktif;
                        @endphp
                        <flux:dropdown position="bottom" align="end">
                            <flux:profile
                                :avatar="$authMember?->photoUrl()"
                                :initials="$authUser->initials()"
                                circle
                                class="shrink-0"
                            />

                            <flux:menu>
                                <flux:menu.radio.group>
                                    <div class="p-0 text-sm font-normal">
                                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-full">
                                                @if ($authMember?->photoUrl())
                                                    <img src="{{ $authMember->photoUrl() }}" alt="{{ $authUser->name }}" class="h-full w-full object-cover" />
                                                @else
                                                    <span class="flex h-full w-full items-center justify-center rounded-full bg-primary-container text-on-primary-container">
                                                        {{ $authUser->initials() }}
                                                    </span>
                                                @endif
                                            </span>
                                            <div class="grid flex-1 text-left text-sm leading-tight">
                                                <span class="truncate font-semibold">{{ $authUser->name }}</span>
                                                <span class="truncate text-xs text-zinc-500">{{ $authUser->email }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </flux:menu.radio.group>

                                <flux:menu.separator />

                                <flux:menu.radio.group>
                                    <flux:menu.item href="{{ route('dashboard') }}" icon="squares-2x2" wire:navigate>{{ __('nav.dashboard') }}</flux:menu.item>
                                    @if ($authMember)
                                        <flux:menu.item href="{{ route('member.profile.edit') }}" icon="user" wire:navigate>Profil Saya</flux:menu.item>
                                        @if ($authMemberActive)
                                            <flux:menu.item href="{{ route('profiles.show', $authMember->slug ?? $authMember->id) }}" icon="identification" wire:navigate>Profil Publik</flux:menu.item>
                                        @endif
                                        <flux:menu.item href="{{ route('member.posts.index') }}" icon="document-duplicate" wire:navigate>Artikel Saya</flux:menu.item>
                                    @endif
                                </flux:menu.radio.group>

                                <flux:menu.separator />

                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                        {{ __('Log Out') }}
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    @else
                        {{-- Keanggotaan tertutup: tautan pendaftaran tidak ditampilkan untuk publik. --}}
                        <a href="{{ route('login') }}" wire:navigate
                           class="hidden sm:inline-flex bg-primary text-on-primary px-7 py-2.5 rounded-full font-label-lg text-label-lg hover:bg-primary/90 hover:shadow-lg hover:shadow-primary/20 transition-all active:scale-95">
                            {{ __('nav.login') }}
                        </a>
                    @endauth
                    <button type="button" class="lg:hidden text-primary" @click="open = !open" aria-label="{{ __('nav.menu') }}">
                        <span class="material-symbols-outlined text-[32px]">menu</span>
                    </button>
                </div>
            </div>

            <div x-show="open" x-transition class="lg:hidden glass-nav border-t border-outline-variant/30 px-margin-mobile py-4 space-y-1" style="display: none;">
                <a href="{{ route('home') }}" wire:navigate class="block py-2 font-label-lg text-label-lg text-on-surface-variant hover:text-primary">{{ __('nav.home') }}</a>

                <p class="pt-2 pb-1 font-label-lg text-label-lg font-bold text-primary uppercase tracking-widest text-[12px]">{{ __('nav.profile') }}</p>
                @foreach ($profilePages as $href => $label)
                    <a href="{{ $href }}" wire:navigate class="block py-2 pl-4 font-label-lg text-label-lg text-on-surface-variant hover:text-primary border-l-2 border-outline-variant/40">{{ $label }}</a>
                @endforeach
                <a href="{{ route('org-chart.show') }}" wire:navigate class="block py-2 pl-4 font-label-lg text-label-lg text-on-surface-variant hover:text-primary border-l-2 border-outline-variant/40">{{ __('nav.org_structure') }}</a>

                <a href="{{ route('posts.index') }}" wire:navigate class="block py-2 font-label-lg text-label-lg text-on-surface-variant hover:text-primary">{{ __('nav.news') }}</a>
                <a href="{{ route('agenda.index') }}" wire:navigate class="block py-2 font-label-lg text-label-lg text-on-surface-variant hover:text-primary">{{ __('nav.agenda') }}</a>
                <a href="{{ route('gallery.index') }}" wire:navigate class="block py-2 font-label-lg text-label-lg text-on-surface-variant hover:text-primary">{{ __('nav.gallery') }}</a>
                <a href="{{ route('contact.show') }}" wire:navigate class="block py-2 font-label-lg text-label-lg text-on-surface-variant hover:text-primary">{{ __('nav.contact') }}</a>

                @guest
                    <div class="pt-3 mt-2 border-t border-outline-variant/30">
                        <a href="{{ route('login') }}" wire:navigate
                           class="block w-full text-center bg-primary text-on-primary px-7 py-2.5 rounded-full font-label-lg text-label-lg hover:bg-primary/90 transition-all">
                            {{ __('nav.login') }}
                        </a>
                    </div>
                @endguest
            </div>
        </nav>

        <main class="pt-[88px]">
            {{ $slot }}
        </main>

        <footer class="w-full pt-24 pb-12 bg-surface-container-high border-t border-outline-variant/30">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12 md:gap-16 mb-20">
                <div class="col-span-2 md:col-span-1 space-y-8">
                    <div class="flex items-center">
                        @if ($site->logoUrl())
                            <img src="{{ $site->logoUrl() }}" alt="{{ $site->site_name }}" class="h-16 w-auto" />
                        @else
                            <x-app-logo-icon class="h-16 w-auto text-primary" />
                        @endif
                    </div>
                    <p class="text-on-surface-variant font-body-md leading-relaxed opacity-80">
                        {{ $site->tagline ?? __('footer.tagline') }}
                    </p>
                </div>

                <div class="space-y-8">
                    <h4 class="text-on-surface font-bold text-headline-md text-[20px] relative after:absolute after:bottom-[-8px] after:left-0 after:w-8 after:h-1 after:bg-primary-container">{{ __('footer.navigation') }}</h4>
                    <ul class="space-y-4">
                        <li><a class="text-on-surface-variant hover:text-primary transition-colors font-body-md hover:translate-x-1 inline-block" href="/tentang" wire:navigate>{{ __('nav.about') }}</a></li>
                        <li><a class="text-on-surface-variant hover:text-primary transition-colors font-body-md hover:translate-x-1 inline-block" href="{{ route('org-chart.show') }}" wire:navigate>{{ __('nav.organization') }}</a></li>
                        <li><a class="text-on-surface-variant hover:text-primary transition-colors font-body-md hover:translate-x-1 inline-block" href="{{ route('gallery.index') }}" wire:navigate>{{ __('nav.gallery') }}</a></li>
                    </ul>
                </div>

                <div class="space-y-8">
                    <h4 class="text-on-surface font-bold text-headline-md text-[20px] relative after:absolute after:bottom-[-8px] after:left-0 after:w-8 after:h-1 after:bg-primary-container">{{ __('footer.important_links') }}</h4>
                    <ul class="space-y-4">
                        <li><a class="text-on-surface-variant hover:text-primary transition-colors font-body-md hover:translate-x-1 inline-block" href="{{ route('posts.index') }}" wire:navigate>{{ __('nav.news') }}</a></li>
                        <li><a class="text-on-surface-variant hover:text-primary transition-colors font-body-md hover:translate-x-1 inline-block" href="{{ route('agenda.index') }}" wire:navigate>{{ __('nav.agenda') }}</a></li>
                        <li><a class="text-on-surface-variant hover:text-primary transition-colors font-body-md hover:translate-x-1 inline-block" href="{{ route('contact.show') }}" wire:navigate>{{ __('nav.contact') }}</a></li>
                    </ul>
                </div>

                <div class="col-span-2 md:col-span-1 space-y-8">
                    <h4 class="text-on-surface font-bold text-headline-md text-[20px] relative after:absolute after:bottom-[-8px] after:left-0 after:w-8 after:h-1 after:bg-primary-container">{{ __('footer.head_office') }}</h4>
                    <div class="space-y-5">
                        <p class="text-on-surface-variant font-body-md flex gap-3">
                            <span class="material-symbols-outlined text-primary shrink-0">location_on</span>
                            <span>{{ $site->address ?? __('footer.address') }}</span>
                        </p>
                        <p class="text-on-surface-variant font-body-md flex gap-3">
                            <span class="material-symbols-outlined text-primary shrink-0">mail</span>
                            <span>{{ $site->email ?? __('footer.email') }}</span>
                        </p>
                        @if ($site->phone)
                            <p class="text-on-surface-variant font-body-md flex gap-3">
                                <span class="material-symbols-outlined text-primary shrink-0">call</span>
                                <span>{{ $site->phone }}</span>
                            </p>
                        @endif
                        @if ($site->facebook || $site->instagram || $site->youtube)
                            <div class="flex items-center gap-4 pt-1">
                                @if ($site->facebook)
                                    <a href="{{ $site->facebook }}" target="_blank" rel="noopener" class="text-on-surface-variant hover:text-primary transition-colors font-label-lg text-label-lg">Facebook</a>
                                @endif
                                @if ($site->instagram)
                                    <a href="{{ $site->instagram }}" target="_blank" rel="noopener" class="text-on-surface-variant hover:text-primary transition-colors font-label-lg text-label-lg">Instagram</a>
                                @endif
                                @if ($site->youtube)
                                    <a href="{{ $site->youtube }}" target="_blank" rel="noopener" class="text-on-surface-variant hover:text-primary transition-colors font-label-lg text-label-lg">YouTube</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-10 border-t border-outline-variant/20 flex flex-col md:flex-row justify-between items-center gap-6">
                <p class="text-on-surface-variant/60 font-label-lg text-center md:text-left">
                    &copy; {{ now()->year }} {{ $site->site_name }}. {{ __('footer.rights') }}
                </p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
