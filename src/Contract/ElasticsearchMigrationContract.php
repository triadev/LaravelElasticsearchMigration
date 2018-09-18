<?php
namespace Triadev\EsMigration\Contract;

use Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;

interface ElasticsearchMigrationContract
{
    /**
     * Migrate
     *
     * @param string $version
     *
     * @throws MigrationAlreadyDone
     * @throws FieldDatatypeMigrationFailed
     * @throws \Throwable
     */
    public function migrate(string $version);
}
