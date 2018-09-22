<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::createIndex(
        'phpunit',
        [
            'phpunit' => [
                'dynamic' => 'strict',
                'properties' => [
                    'title' => [
                        'type' => 'text'
                    ],
                    'count' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ],
        [
            'refresh_interval' => "30s"
        ]
    ),
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::updateIndex(
        'phpunit',
        [
            'phpunit' => [
                'properties' => [
                    'description' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ],
        [
            'index' => [
                'refresh_interval' => "60s"
            ]
        ]
    ),
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::updateIndex(
        'phpunit',
        null,
        [
            'analysis' => [
                'analyzer' => [
                    'content' => [
                        'type' => 'custom',
                        'tokenizer' => 'whitespace'
                    ]
                ]
            ]
        ],
        true
    )
];
