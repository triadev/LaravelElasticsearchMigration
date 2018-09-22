<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::deleteByQuery(
        'examples',
        [
            'match' => [
                'title' => 'Title'
            ]
        ],
        'example',
        [
            'conflicts' => 'proceed'
        ]
    )
];
