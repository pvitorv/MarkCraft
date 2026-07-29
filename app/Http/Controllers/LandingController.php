<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $shortcuts = [
            [
                'label' => 'Post Instagram',
                'hint' => '1:1 · feed',
                'preset' => 'ig_feed_square',
                'tone' => 'teal',
            ],
            [
                'label' => 'Story / Reels',
                'hint' => '9:16 · vertical',
                'preset' => 'ig_story',
                'tone' => 'amber',
            ],
            [
                'label' => 'Thumbnail YouTube',
                'hint' => '16:9 · capa',
                'preset' => 'yt_thumb',
                'tone' => 'rose',
            ],
            [
                'label' => 'Post LinkedIn',
                'hint' => 'feed profissional',
                'preset' => 'li_feed',
                'tone' => 'sky',
            ],
            [
                'label' => 'Capa Facebook',
                'hint' => 'página / perfil',
                'preset' => 'fb_cover',
                'tone' => 'lime',
            ],
            [
                'label' => 'Anúncio quadrado',
                'hint' => 'ads · 1:1',
                'preset' => 'ig_ad_square',
                'tone' => 'orange',
            ],
        ];

        return view('landing', [
            'shortcuts' => $shortcuts,
        ]);
    }
}
