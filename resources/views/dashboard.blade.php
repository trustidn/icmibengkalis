@php
    $user = auth()->user();
    $member = $user->member;
    $profileCompletion = $member ? app(\App\Services\Membership\MemberService::class)->profileCompletionPercentage($member) : null;

    $latestAnnouncements = app(\App\Services\Content\AnnouncementService::class)->active()->take(3);
    $totalPublishedPosts = \App\Models\Post::where('status', \App\Enums\PostStatus::Published)->count();
    $myPostsCount = \App\Models\Post::where('author_id', $user->id)->count();

    $canWritePosts = $user->can('create', \App\Models\Post::class);
    $canManageAnyPost = $user->can('publishing.view');
    $createArticleRoute = match (true) {
        $user->can('publishing.create') => route('admin.publishing.create'),
        $canWritePosts => route('member.posts.create'),
        default => null,
    };
    $manageArticlesRoute = match (true) {
        $canManageAnyPost => route('admin.publishing.index'),
        $member !== null => route('member.posts.index'),
        default => null,
    };
@endphp

<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between gap-4">
            <flux:heading size="lg">Dasbor</flux:heading>
            <div class="flex gap-2">
                @if ($member?->status === \App\Enums\MemberStatus::Aktif)
                    <flux:button :href="route('profiles.show', $member->slug ?? $member->id)" icon="user-circle" variant="outline" wire:navigate>
                        Lihat Profil Publik
                    </flux:button>
                @endif
                <flux:button href="{{ route('home') }}" icon="globe-alt" variant="outline" wire:navigate>
                    Ke Beranda Situs
                </flux:button>
            </div>
        </div>

        @if ($member !== null && $profileCompletion < 100)
            <flux:card class="border-amber-300 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/30">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex-1 min-w-[240px]">
                        <flux:heading size="sm">Lengkapi Profil Anda</flux:heading>
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                            Profil Anda baru {{ $profileCompletion }}% lengkap. Lengkapi data diri, foto, dan bio agar profil publik Anda tampil profesional.
                        </flux:text>
                        <div class="mt-2 h-2 w-full max-w-xs overflow-hidden rounded-full bg-amber-200 dark:bg-amber-900">
                            <div class="h-full rounded-full bg-amber-500" style="width: {{ $profileCompletion }}%"></div>
                        </div>
                    </div>
                    <flux:button :href="route('member.profile.edit')" variant="primary" size="sm" wire:navigate>
                        Lengkapi Sekarang
                    </flux:button>
                </div>
            </flux:card>
        @endif

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Total Artikel Terbit</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $totalPublishedPosts }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Artikel Saya</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $myPostsCount }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-sm text-zinc-500">Pengumuman Aktif</flux:text>
                <flux:heading size="xl" class="mt-1">{{ $latestAnnouncements->count() }}</flux:heading>
            </flux:card>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <flux:card class="md:col-span-2">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">Pengumuman Terbaru</flux:heading>
                    <flux:button :href="route('announcements.index')" size="sm" variant="ghost" wire:navigate>Lihat Semua</flux:button>
                </div>
                <div class="mt-3 flex flex-col gap-3">
                    @forelse ($latestAnnouncements as $announcement)
                        <div class="border-b border-zinc-100 pb-3 last:border-0 last:pb-0 dark:border-zinc-700">
                            <flux:text class="font-medium text-zinc-800 dark:text-zinc-200">{{ $announcement->title }}</flux:text>
                            <flux:text class="mt-0.5 block text-sm text-zinc-500">{{ $announcement->created_at->translatedFormat('d M Y') }}</flux:text>
                        </div>
                    @empty
                        <flux:text class="text-sm text-zinc-500">Belum ada pengumuman aktif.</flux:text>
                    @endforelse
                </div>
            </flux:card>

            <flux:card class="flex flex-col gap-3">
                <flux:heading size="sm">Aksi Cepat</flux:heading>
                @if ($createArticleRoute)
                    <flux:button :href="$createArticleRoute" variant="primary" icon="pencil-square" wire:navigate class="w-full justify-center">
                        Tulis Artikel Baru
                    </flux:button>
                @endif
                @if ($manageArticlesRoute)
                    <flux:button :href="$manageArticlesRoute" variant="outline" icon="document-duplicate" wire:navigate class="w-full justify-center">
                        Kelola Artikel
                    </flux:button>
                @endif
                <flux:button :href="route('archive.index')" variant="outline" icon="archive-box" wire:navigate class="w-full justify-center">
                    Arsip Digital
                </flux:button>
            </flux:card>
        </div>
    </div>
</x-layouts.app>
