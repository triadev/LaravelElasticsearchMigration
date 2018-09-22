<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::createIndex(
        'phpunit_1.0.1',
        [
            'phpunit' => [
                'dynamic' => 'strict',
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
    )
];
