<?php
namespace Triadev\EsMigration\Contract;

interface ElasticsearchMigrationContract
{
    /**
     * Migrate
     *
     * @param string $version
     */
    public function migrate(string $version);
}
