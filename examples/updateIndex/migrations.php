<?php

return [
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
