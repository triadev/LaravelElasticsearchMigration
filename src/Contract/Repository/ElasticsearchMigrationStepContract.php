<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Exception\MigrationsNotExist;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;

interface ElasticsearchMigrationStepContract
{
    const ELASTICSEARCH_MIGRATION_STEP_STATUS_WAIT = 0;
    const ELASTICSEARCH_MIGRATION_STEP_STATUS_RUNNING = 1;
    const ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE = 2;
    const ELASTICSEARCH_MIGRATION_STEP_STATUS_ERROR = 3;
    
    /**
     * Create
     *
     * @param int $migrationId
     * @param string $type
     * @param string $index
     * @return ElasticsearchMigrationStep
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationId,
        string $type,
        string $index
    ) : ElasticsearchMigrationStep;
    
    /**
     * Update
     *
     * @param int $migrationStepId
     * @param int $status
     * @param string|null $error
     * @return ElasticsearchMigrationStep
     *
     * @throws MigrationsNotExist
     * @throws \Throwable
     */
    public function update(
        int $migrationStepId,
        int $status,
        ?string $error = null
    ) : ElasticsearchMigrationStep;
    
    /**
     * Find
     *
     * @param int $migrationStepId
     * @return null|ElasticsearchMigrationStep
     */
    public function find(int $migrationStepId) : ?ElasticsearchMigrationStep;
}
