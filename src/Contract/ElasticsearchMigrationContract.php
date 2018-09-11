<?php
namespace Triadev\EsMigration\Contract;

interface ElasticsearchMigrationContract
{
    /**
     * Migrate
     */
    public function migrate();
}
