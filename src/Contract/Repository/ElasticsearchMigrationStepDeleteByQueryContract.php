<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery;

interface ElasticsearchMigrationStepDeleteByQueryContract
{
    /**
     * Create
     *
     * @param int $migrationStepId
     * @param array $query
     * @param null|string $type
     * @param array $options
     * @return ElasticsearchMigrationStepDeleteByQuery
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationStepId,
        array $query,
        ?string $type = null,
        array $options = []
    ) : ElasticsearchMigrationStepDeleteByQuery;
    
    /**
     * Find
     *
     * @param int $migrationStepId
     * @return null|ElasticsearchMigrationStepDeleteByQuery
     */
    public function find(int $migrationStepId) : ?ElasticsearchMigrationStepDeleteByQuery;
}
