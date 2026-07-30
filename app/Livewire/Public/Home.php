<?php

namespace App\Livewire\Public;

use App\Services\Agenda\AgendaService;
use App\Services\Content\AnnouncementService;
use App\Services\Content\PageService;
use App\Services\Content\PosterService;
use App\Services\Gallery\GalleryService;
use App\Services\Membership\MemberService;
use App\Services\Organization\OrgChartService;
use App\Services\Publishing\PublishingService;
use App\Support\Html;
use Livewire\Component;

class Home extends Component
{
    public function render(
        PublishingService $publishing,
        AnnouncementService $announcements,
        AgendaService $agenda,
        GalleryService $gallery,
        PageService $pages,
        MemberService $members,
        OrgChartService $orgChart,
        PosterService $posters,
    ) {
        $greetingPage = $pages->findBySlug('sambutan-ketua');

        return view('livewire.public.home', [
            'greetingPage' => $greetingPage,
            'greetingExcerpt' => $greetingPage ? Html::excerpt($greetingPage->body, 35) : null,
            'chairman' => $orgChart->chairman(),
            'latestPosts' => collect($publishing->paginatePublished(perPage: 4)->items()),
            'pinnedAnnouncements' => $announcements->active()->where('is_pinned', true)->take(3),
            'posters' => $posters->active(),
            'upcomingEvents' => $agenda->upcoming(perPage: 3)->items(),
            'latestGalleryItems' => $gallery->latestItems(6),
            'featuredMembers' => $members->randomFeatured(5),
        ])->layout('components.layouts.public', [
            'metaTitle' => config('app.name').' — Portal Digital ICMI Kabupaten Bengkalis',
        ]);
    }
}
