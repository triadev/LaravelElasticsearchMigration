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
    )
];
