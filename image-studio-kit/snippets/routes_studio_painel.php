<?php
/**
 * Trecho de referência — routes/web.php (BlogCriaSysWeb).
 * Adaptar prefixo, middleware e nomes no projeto destino.
 *
 * use App\Http\Controllers\Painel\ImageStudioController;
 */

Route::middleware('blog.studio')->group(function () {
    Route::get('studio', [ImageStudioController::class, 'index'])->name('studio');
    Route::get('studio/catalogo', [ImageStudioController::class, 'catalog'])->name('studio.catalogo');
    Route::post('studio/salvar', [ImageStudioController::class, 'store'])->name('studio.salvar');
    Route::post('studio/remover-fundo', [ImageStudioController::class, 'removeBackground'])
        ->middleware('throttle:20,1')
        ->name('studio.remover-fundo');
});
