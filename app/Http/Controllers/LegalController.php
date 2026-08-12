<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalController extends Controller
{
    public function show(string $page): View
    {
        $pages = config('legal.pages', []);
        if (! array_key_exists($page, $pages)) {
            throw new NotFoundHttpException;
        }

        $meta = $pages[$page];

        return view('legal.show', [
            'pageKey' => $page,
            'pageMeta' => $meta,
            'legal' => config('legal'),
            'allPages' => $pages,
        ]);
    }
}
