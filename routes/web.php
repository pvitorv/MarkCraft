<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShortLinkController;
use App\Http\Controllers\StudioController;
use App\Support\MarkCraftShell;
use Illuminate\Support\Facades\Route;

/*
| Dois produtos no mesmo código:
| - web     → página de vendas (landing) no navegador
| - desktop → SEM landing; só login → Studio
*/
if (MarkCraftShell::isDesktop()) {
    Route::get('/', function () {
        return auth()->check()
            ? redirect()->route('studio')
            : redirect()->route('login');
    })->name('home');
} else {
    Route::get('/', [LandingController::class, 'index'])->name('home');
}

Route::get('/s/{code}', [ShortLinkController::class, 'redirect'])
    ->where('code', '[A-Za-z0-9]{4,16}')
    ->name('short.redirect');

Route::post('/api/tools/shorten', [ShortLinkController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('api.tools.shorten');

if (MarkCraftShell::isDesktop()) {
    Route::redirect('/apoiar', '/studio')->name('apoiar');
    Route::redirect('/ferramentas', '/studio')->name('ferramentas.index');
    Route::redirect('/ferramentas/{slug}', '/studio')->name('ferramentas.show');
    Route::redirect('/packs', '/studio')->name('packs.index');
} else {
    Route::redirect('/apoiar', '/?hub=apoiar')->name('apoiar');
    Route::redirect('/ferramentas', '/?hub=ferramentas')->name('ferramentas.index');
    Route::redirect('/ferramentas/{slug}', '/?hub=ferramentas')->name('ferramentas.show');
    Route::redirect('/packs', '/?hub=packs')->name('packs.index');
}

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(MarkCraftShell::homeRouteName());
    })->name('dashboard');

    Route::get('/studio', [StudioController::class, 'index'])->name('studio');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/cms', [\App\Http\Controllers\Admin\CmsController::class, 'index'])->name('cms.index');
    Route::post('/cms', [\App\Http\Controllers\Admin\CmsController::class, 'update'])->name('cms.update');
});

require __DIR__.'/auth.php';
