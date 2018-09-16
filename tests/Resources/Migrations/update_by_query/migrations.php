<?php

return [
    [
        'index' => 'phpunit',
        'updateByQuery' => [
            'type' => 'phpunit',
            'query' => [
                'match' => [
                    'title' => 'Title'
                ]
            ],
            'conflicts' => 'proceed',
            'script' => [
                'source' => 'ctx._source.count++',
                'lang' => 'painless'
            ]
        ]
    ]
];
