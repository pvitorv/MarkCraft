<?php

namespace App\Http\Controllers;

use App\Services\ImageStudio\ImageStudioService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudioController extends Controller
{
    public function index(Request $request, ImageStudioService $studio): View
    {
        $catalog = $studio->catalog(auth()->user());
        $preset = (string) $request->query('preset', '');
        if ($preset === '' || ! preg_match('/^[a-z0-9_]{2,64}$/', $preset)) {
            $preset = null;
        }

        return view('studio.index', [
            'imageStudioCatalog' => $catalog,
            'initialPreset' => $preset,
        ]);
    }
}
