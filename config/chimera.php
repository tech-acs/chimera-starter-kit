<?php
// config for Uneca/Chimera

return [
    'secure' => env('SECURE', false),
    'indicators_per_page' => env('INDICATORS_PER_PAGE', 2),
    'records_per_page' => env('RECORDS_PER_PAGE', 20),
    'dictionaries' => [
        /*'households' => [
            'start_date' => env('LISTING_START_DATE', '2021-05-16'),
            'end_date' => env('LISTING_END_DATE','2021-05-31'),
            'title' => 'Households',
            'color' => 'text-indigo-600',
            'icon' => '',
        ],*/
    ],
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