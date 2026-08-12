<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Documentos legais — MarkCraft (família CriaSys)
    |--------------------------------------------------------------------------
    | Ajuste e-mail e razão social no .env. Textos prontos em resources/views/legal.
    */
    'product_name' => env('APP_NAME', 'MarkCraft'),
    'brand_family' => 'CriaSys',
    'controller_name' => env('LEGAL_CONTROLLER_NAME', 'CriaSys'),
    'contact_email' => env('LEGAL_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'contato@criasysweb.com.br')),
    'site_url' => env('APP_URL', 'https://markcraft.criasysweb.com.br'),
    'last_updated' => env('LEGAL_LAST_UPDATED', '11 de agosto de 2026'),
    'jurisdiction' => 'Brasil',
    'governing_law' => 'leis da República Federativa do Brasil, em especial o Código Civil, o Marco Civil da Internet (Lei nº 12.965/2014) e a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD)',

    'pages' => [
        'privacidade' => [
            'title' => 'Política de Privacidade',
            'nav' => 'Privacidade',
            'description' => 'Como o MarkCraft coleta, usa e protege dados pessoais, em conformidade com a LGPD.',
        ],
        'termos' => [
            'title' => 'Termos de Uso',
            'nav' => 'Termos de uso',
            'description' => 'Condições de uso do studio MarkCraft, contas, conteúdo e limitações de responsabilidade.',
        ],
        'cookies' => [
            'title' => 'Política de Cookies',
            'nav' => 'Cookies',
            'description' => 'Cookies e tecnologias semelhantes usados no MarkCraft para sessão, segurança e métricas.',
        ],
        'uso-aceitavel' => [
            'title' => 'Política de Uso Aceitável',
            'nav' => 'Uso aceitável',
            'description' => 'Regras de conteúdo e conduta no MarkCraft para proteger usuários, terceiros e a plataforma.',
        ],
    ],
];
