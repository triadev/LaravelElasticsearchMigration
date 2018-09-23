<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias;

interface ElasticsearchMigrationsAliasContract
{
    /**
     * Create
     *
     * @param int $migrationsId
     * @param array $add
     * @param array $remove
     * @param array $removeIndices
     * @return ElasticsearchMigrationsAlias
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationsId,
        array $add = [],
        array $remove = [],
        array $removeIndices = []
    ) : ElasticsearchMigrationsAlias;
    
    /**
     * Find
     *
     * @param int $migrationsId
     * @return null|ElasticsearchMigrationsAlias
     */
    public function find(int $migrationsId) : ?ElasticsearchMigrationsAlias;
}
