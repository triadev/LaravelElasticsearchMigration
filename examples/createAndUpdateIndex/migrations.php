<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::createIndex(
        'examples',
        [
            'example' => [
                'properties' => [
                    'title' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ],
        [
            'refresh_interval' => "30s"
        ]
    ),
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::updateIndex(
        'examples',
        [
            'example' => [
                'properties' => [
                    'title' => [
                        'type' => 'keyword'
                    ]
                ]
            ]
        ],
        [
            'index' => [
                'refresh_interval' => "60s"
            ]
        ]
    )
];
