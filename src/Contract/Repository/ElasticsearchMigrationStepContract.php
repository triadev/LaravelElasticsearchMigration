<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Exception\MigrationsNotExist;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;

interface ElasticsearchMigrationStepContract
{
    /**
     * Create
     *
     * @param int $migrationId
     * @param string $type
     * @param array $params
     * @return ElasticsearchMigrationStep
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationId,
        string $type,
        array $params = []
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
