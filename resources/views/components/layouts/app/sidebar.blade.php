<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        @php $site = \App\Models\SiteSetting::current(); @endphp
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mr-5 flex items-center" wire:navigate>
                @if ($site->logoUrl())
                    <img src="{{ $site->logoUrl() }}" alt="{{ $site->site_name }}" class="h-8 w-auto shrink-0" />
                @else
                    <div class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
                    </div>
                @endif
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Platform" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Dashboard</flux:navlist.item>
                    <flux:navlist.item icon="globe-alt" :href="route('home')" wire:navigate>Ke Beranda Situs</flux:navlist.item>

                    @can('pages.manage')
                        <flux:navlist.item icon="document-text" :href="route('admin.pages.index')" :current="request()->routeIs('admin.pages.index')" wire:navigate>Halaman Statis</flux:navlist.item>
                        <flux:navlist.item icon="sparkles" :href="route('admin.posters.index')" :current="request()->routeIs('admin.posters.index')" wire:navigate>Poster Ucapan</flux:navlist.item>
                    @endcan

                    @can('publishing.view')
                        <flux:navlist.item icon="newspaper" :href="route('admin.publishing.index')" :current="request()->routeIs('admin.publishing.*')" wire:navigate>Berita & Artikel</flux:navlist.item>
                    @endcan

                    @can('announcements.manage')
                        <flux:navlist.item icon="megaphone" :href="route('admin.announcements.index')" :current="request()->routeIs('admin.announcements.index')" wire:navigate>Pengumuman</flux:navlist.item>
                    @endcan

                    @can('agenda.manage')
                        <flux:navlist.item icon="calendar" :href="route('admin.agenda.index')" :current="request()->routeIs('admin.agenda.*')" wire:navigate>Agenda</flux:navlist.item>
                    @endcan

                    @can('gallery.manage')
                        <flux:navlist.item icon="photo" :href="route('admin.gallery.index')" :current="request()->routeIs('admin.gallery.*')" wire:navigate>Galeri</flux:navlist.item>
                    @endcan

                    @can('contact.view')
                        <flux:navlist.item icon="envelope" :href="route('admin.contact.index')" :current="request()->routeIs('admin.contact.index')" wire:navigate>Pesan Kontak</flux:navlist.item>
                    @endcan
                </flux:navlist.group>

                @if (auth()->user()->member)
                    <flux:navlist.group heading="Akun Saya" class="grid">
                        <flux:navlist.item icon="user" :href="route('member.profile.edit')" :current="request()->routeIs('member.profile.edit')" wire:navigate>Profil Saya</flux:navlist.item>
                        <flux:navlist.item icon="pencil-square" :href="route('member.posts.create')" :current="request()->routeIs('member.posts.create')" wire:navigate>Tulis Berita/Artikel</flux:navlist.item>
                        <flux:navlist.item icon="document-duplicate" :href="route('member.posts.index')" :current="request()->routeIs(['member.posts.index', 'member.posts.edit'])" wire:navigate>Artikel Saya</flux:navlist.item>
                    </flux:navlist.group>
                @endif

                <flux:navlist.group heading="Keanggotaan" class="grid">
                    @can('members.view')
                        <flux:navlist.item icon="users" :href="route('admin.members.index')" :current="request()->routeIs('admin.members.*')" wire:navigate>Anggota</flux:navlist.item>
                        <flux:navlist.item icon="identification" :href="route('admin.professions.index')" :current="request()->routeIs('admin.professions.index')" wire:navigate>Profesi</flux:navlist.item>
                    @endcan

                    @can('expertise.view')
                        <flux:navlist.item icon="academic-cap" :href="route('admin.expertise.fields')" :current="request()->routeIs('admin.expertise.fields')" wire:navigate>Bidang Keahlian</flux:navlist.item>
                    @endcan

                    @can('organization.view')
                        <flux:navlist.item icon="building-office-2" :href="route('admin.organization.periods')" :current="request()->routeIs('admin.organization.*')" wire:navigate>Struktur Organisasi</flux:navlist.item>
                    @endcan
                </flux:navlist.group>

                <flux:navlist.group heading="Arsip" class="grid">
                    @can('archive.view')
                        <flux:navlist.item icon="archive-box" :href="route('admin.archive.index')" :current="request()->routeIs('admin.archive.*')" wire:navigate>Arsip Digital</flux:navlist.item>
                    @endcan
                </flux:navlist.group>

                @canany(['settings.manage', 'users.manage'])
                    <flux:navlist.group heading="Pengaturan" class="grid">
                        @can('settings.manage')
                            <flux:navlist.item icon="cog-6-tooth" :href="route('admin.settings.site')" :current="request()->routeIs('admin.settings.site')" wire:navigate>Konfigurasi Web</flux:navlist.item>
                        @endcan
                        @can('users.manage')
                            <flux:navlist.item icon="user-group" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.index')" wire:navigate>Manajemen User</flux:navlist.item>
                        @endcan
                    </flux:navlist.group>
                @endcanany
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                        @if (auth()->user()->member)
                            <flux:menu.item href="{{ route('member.profile.edit') }}" icon="user" wire:navigate>Profil Anggota</flux:menu.item>
                            <flux:menu.item href="{{ route('member.posts.create') }}" icon="pencil-square" wire:navigate>Tulis Berita/Artikel</flux:menu.item>
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
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                        @if (auth()->user()->member)
                            <flux:menu.item href="{{ route('member.profile.edit') }}" icon="user" wire:navigate>Profil Anggota</flux:menu.item>
                            <flux:menu.item href="{{ route('member.posts.create') }}" icon="pencil-square" wire:navigate>Tulis Berita/Artikel</flux:menu.item>
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
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
