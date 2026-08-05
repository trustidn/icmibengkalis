<?php

use App\Http\Controllers\IdCardPdfController;
use App\Livewire\Member\IdCards;
use App\Livewire\Member\Posts\Create as PostsCreate;
use App\Livewire\Member\Posts\Index as PostsIndex;
use App\Livewire\Member\Profile\Edit as ProfileEdit;
use Illuminate\Support\Facades\Route;

Route::prefix('akun')->middleware(['auth', 'verified'])->name('member.')->group(function () {
    Route::get('profil', ProfileEdit::class)->name('profile.edit');
    Route::get('opini/baru', PostsCreate::class)->name('posts.create');
    Route::get('artikel', PostsIndex::class)->name('posts.index');
    Route::get('artikel/{post}/ubah', PostsCreate::class)->name('posts.edit');
    Route::get('id-card', IdCards::class)->name('idcard.index');
    Route::get('id-card/{event}/cetak', [IdCardPdfController::class, 'own'])->name('idcard.print');
});
