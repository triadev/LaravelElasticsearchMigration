<?php

return [
    'type' => \Triadev\EsMigration\Business\Mapper\MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
    'params' => [
        'index' => 'index'
    ],
    'priority' => 2,
    'stopOnFailure' => true
];
