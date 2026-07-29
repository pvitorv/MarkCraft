<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Services\ImageStudio\BackgroundRemovalService;
use App\Services\ImageStudio\ImageStudioService;
use App\Support\CurrentBlog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImageStudioController extends Controller
{
    public function __construct(protected CurrentBlog $currentBlog)
    {
    }

    public function index(ImageStudioService $studio): View
    {
        $blog = $this->currentBlog->get();
        $catalog = $studio->catalog($blog);

        return view('painel.studio.index', [
            'imageStudioCatalog' => $catalog,
            'studioSaveUrl' => route('painel.studio.salvar'),
            'studioCatalogUrl' => route('painel.studio.catalogo'),
            'studioRemoveBgUrl' => \Illuminate\Support\Facades\Route::has('painel.studio.remover-fundo')
                ? route('painel.studio.remover-fundo')
                : url('/painel/studio/remover-fundo'),
            'ferramentaAtiva' => 'studio',
        ]);
    }

    public function catalog(ImageStudioService $studio): JsonResponse
    {
        return response()->json($studio->catalog($this->currentBlog->get()));
    }

    /**
     * Remoção de fundo via rembg (servidor). Temporário: apaga arquivos após responder.
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

    /**
     * Grava a arte exportada em storage público do blog (para usar em posts).
     */
    public function store(Request $request): JsonResponse
    {
        $blog = $this->currentBlog->get();

        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimes:png,jpg,jpeg,webp,svg,pdf'],
            'format' => ['nullable', 'string', 'max:16'],
            'preset' => ['nullable', 'string', 'max:64'],
        ]);

        $file = $data['file'];
        $ext = strtolower($file->getClientOriginalExtension() ?: ($data['format'] ?? 'png'));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $name = 'studio_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).'.'.$ext;
        $dir = 'blogs/'.$blog->id.'/studio';
        $path = $file->storeAs($dir, $name, 'public');

        return response()->json([
            'message' => 'Imagem salva na pasta do blog.',
            'export' => [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'filename' => $name,
                'preset' => $data['preset'] ?? null,
            ],
        ]);
    }
}
