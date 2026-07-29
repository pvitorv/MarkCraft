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
                'aspect' => '1/1',
                'icon' => 'square',
            ],
            [
                'label' => 'Story / Reels',
                'hint' => '9:16 · vertical',
                'preset' => 'ig_story',
                'tone' => 'amber',
                'aspect' => '9/16',
                'icon' => 'phone',
            ],
            [
                'label' => 'Thumbnail YouTube',
                'hint' => '16:9 · capa',
                'preset' => 'yt_thumb',
                'tone' => 'rose',
                'aspect' => '16/9',
                'icon' => 'play',
            ],
            [
                'label' => 'Post LinkedIn',
                'hint' => 'feed profissional',
                'preset' => 'li_feed',
                'tone' => 'sky',
                'aspect' => '1.91/1',
                'icon' => 'brief',
            ],
            [
                'label' => 'Capa Facebook',
                'hint' => 'página / perfil',
                'preset' => 'fb_cover',
                'tone' => 'lime',
                'aspect' => '16/9',
                'icon' => 'cover',
            ],
            [
                'label' => 'Studio completo',
                'hint' => 'abrir o editor',
                'preset' => null,
                'tone' => 'neon',
                'featured' => true,
                'aspect' => '1/1',
                'icon' => 'studio',
            ],
        ];

        return view('landing', [
            'shortcuts' => $shortcuts,
        ]);
    }
}
