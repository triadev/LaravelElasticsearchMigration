<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias;

interface ElasticsearchMigrationStepAliasContract
{
    /**
     * Create
     *
     * @param int $migrationStepId
     * @param array $add
     * @param array $remove
     * @param array $removeIndices
     * @return ElasticsearchMigrationStepAlias
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationStepId,
        array $add = [],
        array $remove = [],
        array $removeIndices = []
    ) : ElasticsearchMigrationStepAlias;
    
    /**
     * Find
     *
     * @param int $migrationStepId
     * @return null|ElasticsearchMigrationStepAlias
     */
    public function find(int $migrationStepId) : ?ElasticsearchMigrationStepAlias;
}
