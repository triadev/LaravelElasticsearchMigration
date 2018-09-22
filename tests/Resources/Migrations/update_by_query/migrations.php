<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::updateByQuery(
        'phpunit',
        [
            'match' => [
                'title' => 'Title'
            ]
        ],
        'phpunit',
        [
            'source' => 'ctx._source.count++',
            'lang' => 'painless'
        ],
        [
            'conflicts' => 'proceed'
        ]
    )
];
