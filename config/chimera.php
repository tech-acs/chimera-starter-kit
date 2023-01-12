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
    'require_account_approval' => env('REQUIRE_ACCOUNT_APPROVAL', false),
    'color_theme' => env('COLOR_THEME', 'default'),
    'area' => [
        'map' => [
            'center' => [env('MAP_CENTER_LAT', 9.005401), env('MAP_CENTER_LON', 38.763611)],
            'ignore_orphan_areas' => env('IGNORE_ORPHAN_AREAS', false),
            'level_to_zoom_mapping' => [
                [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
            ],
        ],
    ],

    'cache' => [
        'enabled' => env('CACHE_ENABLED', false),
    ],
];
