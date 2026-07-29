<?php

use App\Http\Controllers\Api\ImageStudioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('api')->group(function () {
    Route::get('image-studio/catalog', [ImageStudioController::class, 'catalog'])->name('api.image-studio.catalog');
    Route::get('image-studio/workspace', [ImageStudioController::class, 'show'])->name('api.image-studio.workspace');
    Route::post('image-studio/remove-background', [ImageStudioController::class, 'removeBackground'])
        ->middleware('throttle:20,1')
        ->name('api.image-studio.remove-background');
});
