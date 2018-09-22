<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::updateByQuery(
        'examples',
        [
            'match' => [
                'title' => 'Title'
            ]
        ],
        'example',
        [
            'source' => 'ctx._source.count++',
            'lang' => 'painless'
        ],
        [
            'conflicts' => 'proceed'
        ]
    )
];
