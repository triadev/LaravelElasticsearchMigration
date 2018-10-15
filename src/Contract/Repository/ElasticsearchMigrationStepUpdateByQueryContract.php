<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery;

interface ElasticsearchMigrationStepUpdateByQueryContract
{
    /**
     * Create
     *
     * @param int $migrationStepId
     * @param array $query
     * @param null|string $type
     * @param array|null $script
     * @param array $options
     * @return ElasticsearchMigrationStepUpdateByQuery
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationStepId,
        array $query,
        ?string $type = null,
        ?array $script = null,
        array $options = []
    ) : ElasticsearchMigrationStepUpdateByQuery;
    
    /**
     * Find
     *
     * @param int $migrationStepId
     * @return null|ElasticsearchMigrationStepUpdateByQuery
     */
    public function find(int $migrationStepId) : ?ElasticsearchMigrationStepUpdateByQuery;
}
