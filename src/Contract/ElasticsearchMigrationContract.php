<?php
namespace Triadev\EsMigration\Contract;

use Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;

interface ElasticsearchMigrationContract
{
    const MIGRATION_SOURCE_TYPE_FILE = 'file';
    const MIGRATION_SOURCE_TYPE_DATABASE = 'database';
    
    /**
     * Migrate
     *
     * @param string $version
     * @param string $source
     *
     * @throws MigrationAlreadyDone
     * @throws FieldDatatypeMigrationFailed
     * @throws \Throwable
     */
    public function migrate(string $version, string $source = self::MIGRATION_SOURCE_TYPE_FILE);
}
