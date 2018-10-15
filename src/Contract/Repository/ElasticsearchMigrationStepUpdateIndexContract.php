<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex;

interface ElasticsearchMigrationStepUpdateIndexContract
{
    /**
     * Create
     *
     * @param int $migrationStepId
     * @param array|null $mappings
     * @param array|null $settings
     * @param bool $closeIndex
     * @return ElasticsearchMigrationStepUpdateIndex
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationStepId,
        ?array $mappings = null,
        ?array $settings = null,
        bool $closeIndex = false
    ) : ElasticsearchMigrationStepUpdateIndex;
    
    /**
     * Find
     *
     * @param int $migrationStepId
     * @return null|ElasticsearchMigrationStepUpdateIndex
     */
    public function find(int $migrationStepId) : ?ElasticsearchMigrationStepUpdateIndex;
}
