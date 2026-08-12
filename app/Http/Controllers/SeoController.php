<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /studio',
            'Disallow: /profile',
            'Disallow: /dashboard',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /verify-email',
            'Disallow: /confirm-password',
            'Disallow: /s/',
            '',
            'Sitemap: '.$base.'/sitemap.xml',
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function sitemap(): Response
    {
        $now = now()->toAtomString();
        $home = URL::to('/');

        $urls = [
            [
                'loc' => $home,
                'changefreq' => 'weekly',
                'priority' => '1.0',
                'lastmod' => $now,
            ],
        ];

        // Páginas públicas “hub” via query — ajudam descoberta sem indexar auth
        foreach (['ferramentas', 'packs', 'apoiar'] as $hub) {
            $urls[] = [
                'loc' => $home.'?hub='.$hub,
                'changefreq' => 'monthly',
                'priority' => '0.6',
                'lastmod' => $now,
            ];
        }

        foreach (array_keys(config('legal.pages', [])) as $slug) {
            $urls[] = [
                'loc' => URL::route('legal.show', $slug),
                'changefreq' => 'yearly',
                'priority' => '0.4',
                'lastmod' => $now,
            ];
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
