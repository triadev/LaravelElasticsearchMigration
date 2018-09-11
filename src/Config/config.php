<?php

return [
    'host' => env('ELASTICSEARCH_HOST', 'localhost'),
    'port' => env('ELASTICSEARCH_PORT', 9200),
    'scheme' => env('ELASTICSEARCH_SCHEME', 'https'),
    'user' => env('ELASTICSEARCH_USER', ''),
    'pass' => env('ELASTICSEARCH_PASS', ''),
    'migration' => [
        'filePath' => null
    ]
];
