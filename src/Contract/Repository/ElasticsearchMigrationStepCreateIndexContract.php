<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex;

interface ElasticsearchMigrationStepCreateIndexContract
{
    /**
     * Create
     *
     * @param int $migrationStepId
     * @param array $mappings
     * @param array|null $settings
     * @return ElasticsearchMigrationStepCreateIndex
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationStepId,
        array $mappings,
        ?array $settings = null
    ) : ElasticsearchMigrationStepCreateIndex;
    
    /**
     * Find
     *
     * @param int $migrationStepId
     * @return null|ElasticsearchMigrationStepCreateIndex
     */
    public function find(int $migrationStepId) : ?ElasticsearchMigrationStepCreateIndex;
}
