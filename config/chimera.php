<?php
// config for Uneca/Chimera

return [
    'secure' => env('SECURE', false),
    'indicators_per_page' => env('INDICATORS_PER_PAGE', 4),
    'records_per_page' => env('RECORDS_PER_PAGE', 20),
    'cache' => [
        'enabled' => env('CACHE_ENABLED', false),
        'ttl' => env('CACHE_TTL', 300)
    ],
    'maps' => [
        // Area => shape simplification factor
        /*'region' => 0.001,
        'constituency' => 0.001,
        'ea' => 0.0001*/
    ],
];
