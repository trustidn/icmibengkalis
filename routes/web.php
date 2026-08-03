<?php

use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\EditorUploadController;
use App\Http\Controllers\SitemapController;
use App\Livewire\Public\Agenda\Index as PublicAgendaIndex;
use App\Livewire\Public\Agenda\Show as PublicAgendaShow;
use App\Livewire\Public\Announcements\Index as PublicAnnouncementsIndex;
use App\Livewire\Public\Archive\Index as PublicArchiveIndex;
use App\Livewire\Public\Archive\Show as PublicArchiveShow;
use App\Livewire\Public\Archive\Upload as PublicArchiveUpload;
use App\Livewire\Public\Contact\Form as PublicContactForm;
use App\Livewire\Public\Gallery\Index as PublicGalleryIndex;
use App\Livewire\Public\Gallery\Show as PublicGalleryShow;
use App\Livewire\Public\Home;
use App\Livewire\Public\OrgChart;
use App\Livewire\Public\Pages\Show as PublicPageShow;
use App\Livewire\Public\Posts\Index as PublicPostIndex;
use App\Livewire\Public\Posts\Show as PublicPostShow;
use App\Livewire\Public\Profiles\Show as PublicProfileShow;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/arsip/{document}/unduh', [DocumentDownloadController::class, 'show'])->name('archive.download');
Route::get('/arsip/{document}/versi/{version}/unduh', [DocumentDownloadController::class, 'show'])->name('archive.download.version');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::post('editor/upload', [EditorUploadController::class, 'store'])
        ->name('editor.upload');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

Route::middleware('track.view')->group(function () {
    Route::get('/', Home::class)->name('home');

    Route::get('/berita', PublicPostIndex::class)->name('posts.index');
    Route::get('/berita/{slug}', PublicPostShow::class)->name('posts.show');
    Route::get('/pengumuman', PublicAnnouncementsIndex::class)->name('announcements.index');
    Route::get('/agenda', PublicAgendaIndex::class)->name('agenda.index');
    Route::get('/agenda/{slug}', PublicAgendaShow::class)->name('agenda.show');
    Route::get('/galeri', PublicGalleryIndex::class)->name('gallery.index');
    Route::get('/galeri/{slug}', PublicGalleryShow::class)->name('gallery.show');
    Route::get('/kontak', PublicContactForm::class)->name('contact.show');
    Route::get('/organisasi/struktur', OrgChart::class)->name('org-chart.show');
    Route::get('/arsip', PublicArchiveIndex::class)->name('archive.index');
    // WAJIB terdaftar SEBELUM /arsip/{slug} agar tidak tertelan catch-all slug.
    Route::get('/arsip/unggah', PublicArchiveUpload::class)->middleware('auth')->name('archive.upload');
    Route::get('/arsip/{slug}', PublicArchiveShow::class)->name('archive.show');
    Route::get('/profil/{identifier}', PublicProfileShow::class)->name('profiles.show');

    // Halaman statis publik — harus tetap jadi route publik paling akhir (catch-all slug).
    Route::get('/{slug}', PublicPageShow::class)->name('pages.show');
});
