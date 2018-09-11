<?php
namespace Triadev\EsMigration\Contract;

use Triadev\EsMigration\Exception\MigrationAlreadyDone;

interface ElasticsearchMigrationContract
{
    /**
     * Migrate
     *
     * @param string $version
     *
     * @throws MigrationAlreadyDone
     * @throws \Throwable
     */
    public function migrate(string $version);
}
