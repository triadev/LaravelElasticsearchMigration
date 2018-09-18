<?php

return [
    [
        'index' => 'phpunit',
        'type' => 'update',
        'mappings' => [
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
    ]
];
