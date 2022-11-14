<?php
// config for Uneca/census-dashboard-starter-kit

return [
    'secure' => env('SECURE', false),
    'indicators_per_page' => env('INDICATORS_PER_PAGE', 2),
    'records_per_page' => env('RECORDS_PER_PAGE', 20),
    'emailing_enabled' => env('EMAILING_ENABLED', false),
    'enforce_2fa' => env('ENFORCE_2FA', false),
    'invitation' => [
        'ttl_hours' => env('INVITATION_TTL_HOURS', 72)
    ],
    'color_theme' => env('COLOR_THEME', 'default'),
    'developer_mode' => env('DEVELOPER_MODE', false),
    'area' => [
        'hierarchies' => [
            'region',
            'constituency',
            'ea',
        ],
        'map' => [
            'center' => [env('MAP_CENTER_LAT', 9.005401), env('MAP_CENTER_LON', 38.763611)],
        ],
    ],

    'cache' => [
        'enabled' => env('CACHE_ENABLED', false),
        'ttl' => env('CACHE_TTL', 300)
    ],
    'templates_path' => env('TEMPLATES_PATH', 'app/Http/Livewire/IndicatorTemplate'),
];
