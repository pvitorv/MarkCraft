<?php

namespace App\Http\Controllers;

use App\Services\ImageStudio\ImageStudioService;
use App\Support\StudioLayout;
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

        $view = StudioLayout::usesMobileStudio($request)
            ? 'studio-mobile.index'
            : 'studio.index';

        return view($view, [
            'imageStudioCatalog' => $catalog,
            'initialPreset' => $preset,
            'studioLayout' => StudioLayout::layoutName($request),
        ]);
    }
}
