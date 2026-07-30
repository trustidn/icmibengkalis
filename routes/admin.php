<?php

use App\Livewire\Admin\Agenda\Form as AgendaForm;
use App\Livewire\Admin\Agenda\Index as AgendaIndex;
use App\Livewire\Admin\Announcements\Index as AnnouncementsIndex;
use App\Livewire\Admin\Archive\Categories as ArchiveCategories;
use App\Livewire\Admin\Archive\Form as ArchiveForm;
use App\Livewire\Admin\Archive\Index as ArchiveIndex;
use App\Livewire\Admin\Contact\Index as ContactIndex;
use App\Livewire\Admin\Expertise\Fields as ExpertiseFields;
use App\Livewire\Admin\Gallery\Albums as GalleryAlbums;
use App\Livewire\Admin\Gallery\Form as GalleryForm;
use App\Livewire\Admin\Members\Form as MembersForm;
use App\Livewire\Admin\Members\Import as MembersImport;
use App\Livewire\Admin\Members\Index as MembersIndex;
use App\Livewire\Admin\Organization\AssignmentForm;
use App\Livewire\Admin\Organization\Periods as OrganizationPeriods;
use App\Livewire\Admin\Organization\UnitTree;
use App\Livewire\Admin\Pages\Editor as PagesEditor;
use App\Livewire\Admin\Professions\Index as ProfessionsIndex;
use App\Livewire\Admin\Publishing\Form as PublishingForm;
use App\Livewire\Admin\Publishing\Index as PublishingIndex;
use App\Livewire\Admin\Publishing\ReviewQueue;
use App\Livewire\Admin\Posters\Index as PostersIndex;
use App\Livewire\Admin\Settings\SiteConfig;
use App\Livewire\Admin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {
    Route::get('halaman', PagesEditor::class)
        ->middleware('can:pages.manage')
        ->name('pages.index');

    Route::middleware('can:publishing.view')->prefix('berita')->name('publishing.')->group(function () {
        Route::get('/', PublishingIndex::class)->name('index');
        Route::get('/baru', PublishingForm::class)->middleware('can:publishing.create')->name('create');
        Route::get('/antrean-review', ReviewQueue::class)->middleware('can:publishing.review')->name('review-queue');
        Route::get('/{post}/ubah', PublishingForm::class)->middleware('can:publishing.update')->name('edit');
    });

    Route::get('pengumuman', AnnouncementsIndex::class)
        ->middleware('can:announcements.manage')
        ->name('announcements.index');

    Route::middleware('can:agenda.manage')->prefix('agenda')->name('agenda.')->group(function () {
        Route::get('/', AgendaIndex::class)->name('index');
        Route::get('/baru', AgendaForm::class)->name('create');
        Route::get('/{event}/ubah', AgendaForm::class)->name('edit');
    });

    Route::middleware('can:gallery.manage')->prefix('galeri')->name('gallery.')->group(function () {
        Route::get('/', GalleryAlbums::class)->name('index');
        Route::get('/{album}/kelola', GalleryForm::class)->name('edit');
    });

    Route::get('kontak', ContactIndex::class)
        ->middleware('can:contact.view')
        ->name('contact.index');

    Route::middleware('can:members.view')->prefix('anggota')->name('members.')->group(function () {
        Route::get('/', MembersIndex::class)->name('index');
        Route::get('/baru', MembersForm::class)->middleware('can:members.create')->name('create');
        Route::get('/impor', MembersImport::class)->middleware('can:members.import')->name('import');
        Route::get('/{member}/ubah', MembersForm::class)->middleware('can:members.update')->name('edit');
    });

    Route::get('profesi', ProfessionsIndex::class)
        ->middleware('can:members.view')
        ->name('professions.index');

    Route::get('bidang-keahlian', ExpertiseFields::class)
        ->middleware('can:expertise.view')
        ->name('expertise.fields');

    Route::middleware('can:organization.view')->prefix('organisasi')->name('organization.')->group(function () {
        Route::get('/', OrganizationPeriods::class)->name('periods');
        Route::get('/{period}/unit', UnitTree::class)->name('units');
        Route::get('/unit/{unit}/penugasan', AssignmentForm::class)->name('assignments');
    });

    Route::middleware('can:archive.view')->prefix('arsip')->name('archive.')->group(function () {
        Route::get('/', ArchiveIndex::class)->name('index');
        Route::get('/baru', ArchiveForm::class)->middleware('can:archive.create')->name('create');
        Route::get('/kategori', ArchiveCategories::class)->middleware('can:archive.view')->name('categories');
        Route::get('/{document}/kelola', ArchiveForm::class)->middleware('can:archive.update')->name('edit');
    });

    // Poster ucapan beranda — pengelola konten situs.
    Route::get('poster', PostersIndex::class)
        ->middleware('can:pages.manage')
        ->name('posters.index');

    // Konfigurasi web — permission settings.manage dimiliki super-admin & admin-web.
    Route::get('konfigurasi', SiteConfig::class)
        ->middleware('can:settings.manage')
        ->name('settings.site');

    // Manajemen user — permission users.manage dimiliki super-admin & admin-web.
    Route::get('pengguna', UsersIndex::class)
        ->middleware('can:users.manage')
        ->name('users.index');
});
