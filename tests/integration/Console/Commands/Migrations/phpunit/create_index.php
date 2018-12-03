<?php

return [
    'type' => \Triadev\EsMigration\Business\Mapper\MigrationTypes::MIGRATION_TYPE_CREATE_INDEX,
    'params' => [
        'index' => 'index',
        'body' => [
            'mappings' => [
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
            'settings' => [
                'refresh_interval' => "30s"
            ]
        ]
    ],
    'priority' => 1,
    'stopOnFailure' => true
];
