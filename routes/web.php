<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::livewire('share/{sharable_id}', 'pages::game.share')->name('game.share');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('games', 'pages::game.index')->name('game.index');
    Route::livewire('games/{slug}', 'pages::game.show')->name('game.show');
});

require __DIR__.'/settings.php';
