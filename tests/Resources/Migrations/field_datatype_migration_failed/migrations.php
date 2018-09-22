<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::updateIndex(
        'phpunit',
        [
            'phpunit' => [
                'properties' => [
                    'title' => [
                        'type' => 'integer'
                    ],
                    'count' => [
                        'type' => 'text'
                    ],
                    'description' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ]
    )
];
