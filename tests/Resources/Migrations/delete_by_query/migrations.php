<?php

return [
    \Triadev\EsMigration\Business\Factory\MigrationBuilder::deleteByQuery(
        'phpunit',
        [
            'match' => [
                'title' => 'Title'
            ]
        ],
        'phpunit',
        [
            'conflicts' => 'proceed'
        ]
    )
];
