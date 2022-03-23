<?php
// config for Uneca/census-dashboard-starter-kit

return [
    'secure' => env('SECURE', false),
    'indicators_per_page' => env('INDICATORS_PER_PAGE', 4),
    'records_per_page' => env('RECORDS_PER_PAGE', 20),
    'map' => [
        'enabled' => env('ENABLE_MAP', false),
        'shape_simplification' => [
            /*'region' => 0.001,
            'constituency' => 0.001,
            'ea' => 0.0001*/
        ],
    ],
    'reports' => [
        'enabled' => env('ENABLE_REPORTS', false),
    ],
    'cache' => [
        'enabled' => env('CACHE_ENABLED', false),
        'ttl' => env('CACHE_TTL', 300)
    ],
];
