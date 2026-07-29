<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShortLinkController;
use App\Http\Controllers\StudioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/s/{code}', [ShortLinkController::class, 'redirect'])
    ->where('code', '[A-Za-z0-9]{4,16}')
    ->name('short.redirect');

Route::post('/api/tools/shorten', [ShortLinkController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('api.tools.shorten');

// Hub agora abre em modais glass na home (e no Studio) — rotas antigas redirecionam
Route::redirect('/apoiar', '/?hub=apoiar')->name('apoiar');
Route::redirect('/ferramentas', '/?hub=ferramentas')->name('ferramentas.index');
Route::redirect('/ferramentas/{slug}', '/?hub=ferramentas')->name('ferramentas.show');
Route::redirect('/packs', '/?hub=packs')->name('packs.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');
    Route::get('/studio', [StudioController::class, 'index'])->name('studio');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
