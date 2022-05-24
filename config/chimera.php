<?php
// config for Uneca/census-dashboard-starter-kit

return [
    'secure' => env('SECURE', false),
    'indicators_per_page' => env('INDICATORS_PER_PAGE', 2),
    'records_per_page' => env('RECORDS_PER_PAGE', 20),
    'map' => [
        'shape_simplification' => [
            'region' => 0.001,
            'constituency' => 0.001,
            'ea' => 0.0001
        ],
        'center' => [9.005401, 38.763611],
    ],
    'cache' => [
        'enabled' => env('CACHE_ENABLED', false),
        'ttl' => env('CACHE_TTL', 300)
    ],
];
