<?php

return [
    'family' => 'CriaSys',

    /*
    |--------------------------------------------------------------------------
    | Posicionamento (interno — não exibir estes rótulos na UI)
    |--------------------------------------------------------------------------
    | MarkCraft = studio gratuito de entrada na linha CriaSys.
    | Blog CriaSys Web = plataforma completa (blog, painel, billing, afiliados,
    | landings + Image Studio no fluxo de conteúdo).
    */
    'role' => 'entry', // entry | product
    'tagline' => 'Studio de imagem gratuito da família CriaSys — o mesmo DNA do editor do Blog CriaSys Web.',

    'blog' => [
        'name' => 'Blog CriaSys Web',
        'eyebrow' => 'Plataforma completa · CriaSys',
        'headline' => 'Blog, painel e Image Studio no mesmo fluxo',
        'blurb' => 'Publique posts, organize afiliados, capture leads, cobre assinatura e produza capas e artes no editor embutido — sem juntar cinco ferramentas soltas.',
        'cta' => 'Conhecer o Blog CriaSys Web',
        'url' => env('BLOG_CRIASYS_URL', '#'),
        'register_url' => env('BLOG_CRIASYS_REGISTER_URL', env('BLOG_CRIASYS_URL', '#')),
        'cta_ready' => (bool) env('BLOG_CRIASYS_CTA_READY', false),
        'cta_pending' => 'Página de vendas em breve',
        'early_access_note' => 'Acesso antecipado em teste fechado · depoimentos entram só com feedback real',
        'register_cta' => 'Começar teste grátis',
        'continue_studio_cta' => 'Continuar no Studio',
        'create_account_cta' => 'Criar conta no MarkCraft',
        'studio_url' => '',
        'markcraft_register_url' => '',
        'bullets' => [
            'Multi-blog com painel profissional',
            'Mercado Pago, afiliados e landings',
            'Image Studio integrado (mesmo DNA do MarkCraft)',
        ],
    ],

    'rembg_python' => env('REMBG_PYTHON'),

    /*
    |--------------------------------------------------------------------------
    | Shell: web (público) | desktop (cópia local)
    |--------------------------------------------------------------------------
    | web: landing de vendas em `/`.
    | desktop: SEM landing — `/` vai para login → Studio.
    | Cadastro no desktop aberto por padrão (MARKCRAFT_ALLOW_REGISTER=false fecha).
    | Electron em /desktop grava exports em MARKCRAFT_EXPORTS_DIR.
    */
    'shell' => [
        'mode' => env('MARKCRAFT_SHELL', 'web'), // web | desktop
        'allow_register' => env('MARKCRAFT_ALLOW_REGISTER'), // true|false|null (null no desktop = aberto)
        'exports_dir' => env('MARKCRAFT_EXPORTS_DIR', ''), // vazio = ~/MarkCraftExports
        'app_url' => env('MARKCRAFT_DESKTOP_URL', env('APP_URL', 'http://127.0.0.1:8000')),
    ],

    'donations' => [
        'min_brl' => 2,
        'pix_key' => env('DONATION_PIX_KEY', ''),
        'gateway_url' => env('DONATION_GATEWAY_URL', ''),
        'button_label' => 'Contribuir a partir de R$ {min}',
        'modal_title' => 'Apoiar o MarkCraft',
        'modal_intro' => 'Contribuição opcional a partir de R$ {min} — ajuda a manter o studio gratuito no ar.',
        'modal_body' => 'O MarkCraft é o studio gratuito da família CriaSys. Manter servidores e melhorias tem custo — um “obrigado” opcional ajuda.',
        'modal_note' => 'Packs e afiliados controlados no CMS — sem anúncios genéricos soltos.',
        'page_title' => 'Ajude o MarkCraft a continuar',
        'page_body_1' => 'O MarkCraft é gratuito para quem cria, vende e experimenta. Manter servidores, ferramentas e melhorias tem custo.',
        'page_body_2' => 'Se esta ferramenta te ajudou e você quiser que ela continue existindo, considere uma contribuição a partir de R$ {min} — via Pix ou cartão.',
        'page_body_3' => 'Não é obrigatório. É um “obrigado” opcional de quem acredita no projeto.',
    ],

    'packs_hub' => [
        'title' => 'Packs CriaSys',
        'subtitle' => 'Produtos da linha CriaSys — Blog, packs e planos. Sem anúncios genéricos.',
        'link_label' => 'Ver oferta →',
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
            'title' => 'Blog CriaSys Web',
            'blurb' => 'Plataforma completa: blog, painel, cobrança, afiliados, landings e Image Studio no fluxo.',
            'affiliate_url' => env('BLOG_CRIASYS_URL', 'https://criasysweb.com.br'),
            'tag' => 'Plataforma',
        ],
        [
            'title' => 'Packs editoriais CriaSys',
            'blurb' => 'Templates e pacotes em R$ para importar no Studio — identidade, capas e vendas.',
            'affiliate_url' => env('CRIASYS_PACKS_URL', env('BLOG_CRIASYS_URL', 'https://criasysweb.com.br')),
            'tag' => 'CriaSys',
        ],
        [
            'title' => 'Pro Studio no Blog',
            'blurb' => 'Planos, trial e studio premium dentro do Blog CriaSys Web quando você precisar de mais.',
            'affiliate_url' => env('BLOG_CRIASYS_URL', 'https://criasysweb.com.br'),
            'tag' => 'Plano',
        ],
    ],

    'promos' => [
        'landing_mid' => [
            'enabled' => true,
            'eyebrow' => 'Família CriaSys',
            'title' => 'Do studio gratuito à plataforma completa',
            'blurb' => 'No MarkCraft você edita e exporta artes. No Blog CriaSys Web você publica, monetiza e mantém o Image Studio no fluxo do conteúdo.',
            'cta' => 'Ver o Blog CriaSys Web',
            'url' => env('BLOG_CRIASYS_URL', 'https://criasysweb.com.br'),
        ],
        'studio_top' => [
            'enabled' => true,
            'eyebrow' => 'CriaSys',
            'title' => 'Precisa de blog, cobrança e studio juntos?',
            'blurb' => 'MarkCraft edita. Blog CriaSys Web opera o conteúdo ponta a ponta.',
            'cta' => 'Ver Blog',
            'url' => env('BLOG_CRIASYS_URL', 'https://criasysweb.com.br'),
        ],
        'studio_sidebar' => [
            'enabled' => true,
            'eyebrow' => 'Plataforma CriaSys',
            'title' => 'Blog CriaSys Web',
            'blurb' => 'Publique, cobre e produza artes no mesmo ambiente — com o DNA deste studio integrado ao blog.',
            'cta' => 'Conhecer',
            'url' => env('BLOG_CRIASYS_URL', 'https://criasysweb.com.br'),
        ],
    ],
];
