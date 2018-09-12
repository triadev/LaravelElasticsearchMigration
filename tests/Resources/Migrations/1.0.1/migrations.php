<?php

return [
    [
        'index' => 'phpunit_1.0.1',
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
    ]
];
