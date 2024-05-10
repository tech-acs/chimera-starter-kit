<?php
// config for Uneca/census-dashboard-starter-kit

return [
    'owner' => [
        'name' => env('APP_OWNER_NAME', 'ECA'),
        'url' => env('APP_OWNER_URL', '#'),
    ],
    'secure' => env('SECURE', false),
    'indicators_per_page' => env('INDICATORS_PER_PAGE', 2),
    'records_per_page' => env('RECORDS_PER_PAGE', 20),
    'emailing_enabled' => env('EMAILING_ENABLED', false),
    'enforce_2fa' => env('ENFORCE_2FA', false),
    'invitation' => [
        'ttl_hours' => env('INVITATION_TTL_HOURS', 72)
    ],
    'require_account_approval' => env('REQUIRE_ACCOUNT_APPROVAL', false),
    'indicator_template' => [
        'repository_url' => env('INDICATOR_TEMPLATE_REPOSITORY_URL','https://api.github.com/repos/tech-acs/chimera-indicator-templates')
    ],
    'color_theme' => env('COLOR_THEME', 'default'),
    'area' => [
        'map' => [
            'center' => [env('MAP_CENTER_LAT', 9.005401), env('MAP_CENTER_LON', 38.763611)],
            'starting_zoom' => env('MAP_STARTING_ZOOM', 6),
            'min_zoom' => env('MAP_MIN_ZOOM', 6),
            'ignore_orphan_areas' => env('IGNORE_ORPHAN_AREAS', false),
        ],
    ],
    'cache' => [
        'enabled' => env('CACHE_ENABLED', false),
        'ttl' => env('CACHE_TTL_SECONDS', 60 * 60),
        'tags' => [],
    ],
    'long_query_time' => env('LONG_QUERY_TIME', 5), // Seconds
];
