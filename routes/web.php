<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('games', 'game.index')->name('game.index');
    Route::livewire('games/{slug}', 'game.show')->name('game.show');
});

require __DIR__.'/settings.php';require __DIR__ . '/settings.php';
