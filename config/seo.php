<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO MarkCraft — páginas públicas indexáveis
    |--------------------------------------------------------------------------
    */
    'site_name' => env('SEO_SITE_NAME', env('APP_NAME', 'MarkCraft')),

    'default_title' => env(
        'SEO_DEFAULT_TITLE',
        'MarkCraft — studio de imagem gratuito · família CriaSys'
    ),

    'default_description' => env(
        'SEO_DEFAULT_DESCRIPTION',
        'Editor de imagem gratuito da família CriaSys. Layouts, elementos, remoção de fundo e export PNG/JPG/PDF no navegador — sem instalar.'
    ),

    'default_keywords' => env(
        'SEO_DEFAULT_KEYWORDS',
        'editor de imagem online, studio gratuito, criar post instagram, thumbnail youtube, canva gratuito, CriaSys, MarkCraft'
    ),

    'og_image' => env('SEO_OG_IMAGE', '/brand/icon-512.png'),

    'twitter_handle' => env('SEO_TWITTER_HANDLE', ''),

    'locale' => 'pt_BR',
    'og_locale' => 'pt_BR',

    'google_site_verification' => env('SEO_GOOGLE_SITE_VERIFICATION', ''),
    'bing_site_verification' => env('SEO_BING_SITE_VERIFICATION', ''),

    'organization' => [
        'name' => 'MarkCraft',
        'legal_name' => 'CriaSys',
        'url' => null, // usa APP_URL
        'same_as' => array_values(array_filter([
            env('SEO_SAME_AS_BLOG', env('BLOG_CRIASYS_URL', 'https://blog.criasysweb.com.br')),
        ])),
    ],
];
