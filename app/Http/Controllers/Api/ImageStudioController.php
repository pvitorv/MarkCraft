<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageStudio\BackgroundRemovalService;
use App\Services\ImageStudio\ImageStudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ImageStudioController extends Controller
{
    public function catalog(Request $request, ImageStudioService $studio): JsonResponse
    {
        return response()->json($studio->catalog($request->user()));
    }

    public function show(Request $request, ImageStudioService $studio): JsonResponse
    {
        return response()->json($studio->emptyDesign($request->query('preset')));
    }

    /**
     * Remoção de fundo via rembg (servidor). Temporário: apaga após responder.
     */
    public function removeBackground(Request $request, BackgroundRemovalService $remover): JsonResponse|Response
    {
        if ($remover->driver() !== 'rembg') {
            return response()->json([
                'message' => 'Driver rembg não está ativo. Defina IMAGE_STUDIO_BG_REMOVAL_DRIVER=rembg.',
            ], 422);
        }

        if (! $remover->isAvailable()) {
            return response()->json([
                'message' => 'rembg indisponível. Instale Python 3 e rode: pip install rembg pillow onnxruntime',
            ], 503);
        }

        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:png,jpg,jpeg,webp'],
        ]);

        $tmpDir = storage_path('app/tmp/studio-bg');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $token = Str::lower(Str::random(10));
        $input = $tmpDir.DIRECTORY_SEPARATOR.'in_'.$token.'.bin';
        $output = $tmpDir.DIRECTORY_SEPARATOR.'out_'.$token.'.png';

        try {
            $data['file']->move(dirname($input), basename($input));
            $remover->remove($input, $output);

            $png = file_get_contents($output);
            if ($png === false) {
                throw new \RuntimeException('Não foi possível ler o PNG gerado.');
            }

            return response($png, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename="sem-fundo.png"',
                'Cache-Control' => 'no-store',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Falha ao remover fundo.',
            ], 500);
        } finally {
            @unlink($input);
            @unlink($output);
        }
    }
}
