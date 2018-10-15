<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex;

interface ElasticsearchMigrationStepReindexContract
{
    /**
     * Create
     *
     * @param int $migrationStepId
     * @param string $destIndex
     * @param bool $refreshSourceIndex
     * @param array $global
     * @param array $source
     * @param array $dest
     * @return ElasticsearchMigrationStepReindex
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationStepId,
        string $destIndex,
        bool $refreshSourceIndex = false,
        array $global = [],
        array $source = [],
        array $dest = []
    ) : ElasticsearchMigrationStepReindex;
    
    /**
     * Find
     *
     * @param int $migrationStepId
     * @return null|ElasticsearchMigrationStepReindex
     */
    public function find(int $migrationStepId) : ?ElasticsearchMigrationStepReindex;
}
