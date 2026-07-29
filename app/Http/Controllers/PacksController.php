<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PacksController extends Controller
{
    public function index(): View
    {
        $packs = [
            [
                'title' => 'Pack PSD — Posts Instagram',
                'blurb' => 'Templates editáveis no MarkCraft (PSD + PNG).',
                'affiliate_url' => '#',
                'tag' => 'Afiliado',
            ],
            [
                'title' => 'Pack Stories & Reels',
                'blurb' => 'Capas e carrosséis prontos para marketing.',
                'affiliate_url' => '#',
                'tag' => 'Afiliado',
            ],
            [
                'title' => 'Hospedagem (Hostinger)',
                'blurb' => 'Publique o site da sua marca — recomendação comercial.',
                'affiliate_url' => '#',
                'tag' => 'Hospedagem',
            ],
        ];

        return view('packs.index', compact('packs'));
    }
}
