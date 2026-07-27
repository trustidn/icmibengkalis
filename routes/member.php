<?php

use App\Livewire\Member\Posts\Create as PostsCreate;
use App\Livewire\Member\Posts\Index as PostsIndex;
use App\Livewire\Member\Profile\Edit as ProfileEdit;
use Illuminate\Support\Facades\Route;

Route::prefix('akun')->middleware(['auth', 'verified'])->name('member.')->group(function () {
    Route::get('profil', ProfileEdit::class)->name('profile.edit');
    Route::get('opini/baru', PostsCreate::class)->name('posts.create');
    Route::get('artikel', PostsIndex::class)->name('posts.index');
    Route::get('artikel/{post}/ubah', PostsCreate::class)->name('posts.edit');
});
