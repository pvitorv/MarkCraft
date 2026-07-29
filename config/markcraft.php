<?php

return [
    'family' => 'CriaSys',
    'rembg_python' => env('REMBG_PYTHON'),
    'donations' => [
        'min_brl' => 2,
        'pix_key' => env('DONATION_PIX_KEY', ''),
        'gateway_url' => env('DONATION_GATEWAY_URL', ''),
    ],
    'tools' => [
        'encurtador' => [
            'name' => 'Encurtador de URL',
            'short' => 'Encurtar',
            'blurb' => 'Encurte links de campanha com UTM opcional.',
            'status' => 'pronta',
            'icon' => 'link',
        ],
        'conversor-imagens' => [
            'name' => 'Conversor de imagens',
            'short' => 'Imagens',
            'blurb' => 'PNG ↔ JPG ↔ WebP em lote no navegador.',
            'status' => 'pronta',
            'icon' => 'image',
        ],
        'conversor-pdf' => [
            'name' => 'Conversor de PDF',
            'short' => 'PDF',
            'blurb' => 'PDF → imagens ou imagens → PDF.',
            'status' => 'pronta',
            'icon' => 'file',
        ],
        'compressor-pdf' => [
            'name' => 'Compressor de PDF',
            'short' => 'Compactar',
            'blurb' => 'Reduza tamanho reexportando as páginas.',
            'status' => 'pronta',
            'icon' => 'compress',
        ],
    ],
    'affiliate_packs' => [
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
    ],
];
