<?php

return [
    'enabled' => (bool) env('ADS_ENABLED', false),
    'provider' => env('ADS_PROVIDER', 'none'),
    'publisher_id' => env('ADS_PUBLISHER_ID', ''),

    'slots' => [
        'ad_landing_hero' => [
            'enabled' => true,
            'type' => 'mixed',
            'label' => 'Landing — abaixo do hero',
        ],
        'ad_landing_mid' => [
            'enabled' => true,
            'type' => 'mixed',
            'label' => 'Landing — meio da página',
        ],
        'ad_app_sidebar' => [
            'enabled' => true,
            'type' => 'mixed',
            'label' => 'App — coluna lateral',
        ],
        'ad_app_top' => [
            'enabled' => true,
            'type' => 'own',
            'label' => 'App — barra superior (packs)',
        ],
        'ad_app_between_tools' => [
            'enabled' => true,
            'type' => 'mixed',
            'label' => 'Hub ferramentas — entre cards',
        ],
        'ad_packs_inline' => [
            'enabled' => true,
            'type' => 'mixed',
            'label' => 'Packs / afiliados — inline',
        ],
    ],
];
