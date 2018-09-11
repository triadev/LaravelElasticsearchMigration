<?php

return [
    [
        'index' => 'phpunit',
        'type' => 'create',
        'mappings' => [
            'phpunit' => [
                'dynamic' => 'strict',
                'properties' => [
                    'title1' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ],
        'settings' => [
            'refresh_interval' => "30s"
        ]
    ],
    [
        'index' => 'phpunit',
        'type' => 'update',
        'mappings' => [
            'phpunit' => [
                'properties' => [
                    'title2' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ],
        'settings' => [
            'refresh_interval' => "60s"
        ]
    ]
];
