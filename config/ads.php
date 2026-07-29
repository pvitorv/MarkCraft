<?php

return [
    /*
    | AdSense / ads genéricos desligados por padrão.
    | O MarkCraft divulga produtos próprios CriaSys (ver config/markcraft.php).
    */
    'enabled' => (bool) env('ADS_ENABLED', false),
    'provider' => env('ADS_PROVIDER', 'none'),
    'publisher_id' => env('ADS_PUBLISHER_ID', ''),

    'slots' => [
        'ad_landing_hero' => [
            'enabled' => false,
            'type' => 'own',
            'label' => '(legado) Landing — abaixo do hero',
        ],
        'ad_landing_mid' => [
            'enabled' => false,
            'type' => 'own',
            'label' => '(legado) Landing — meio',
        ],
        'ad_app_sidebar' => [
            'enabled' => false,
            'type' => 'own',
            'label' => '(legado) App — lateral',
        ],
        'ad_app_top' => [
            'enabled' => false,
            'type' => 'own',
            'label' => '(legado) App — topo',
        ],
        'ad_app_between_tools' => [
            'enabled' => false,
            'type' => 'own',
            'label' => '(legado) Hub ferramentas',
        ],
        'ad_packs_inline' => [
            'enabled' => false,
            'type' => 'own',
            'label' => '(legado) Packs inline',
        ],
    ],
];
