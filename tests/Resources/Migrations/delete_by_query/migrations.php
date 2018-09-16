<?php

return [
    [
        'index' => 'phpunit',
        'deleteByQuery' => [
            'type' => 'phpunit',
            'query' => [
                'match' => [
                    'title' => 'Title'
                ]
            ],
            'conflicts' => 'proceed'
        ]
    ]
];
