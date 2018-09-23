<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery;

interface ElasticsearchMigrationsDeleteByQueryContract
{
    /**
     * Create
     *
     * @param int $migrationsId
     * @param array $query
     * @param null|string $type
     * @param array $options
     * @return ElasticsearchMigrationsDeleteByQuery
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationsId,
        array $query,
        ?string $type = null,
        array $options = []
    ) : ElasticsearchMigrationsDeleteByQuery;
    
    /**
     * Find
     *
     * @param int $migrationsId
     * @return null|ElasticsearchMigrationsDeleteByQuery
     */
    public function find(int $migrationsId) : ?ElasticsearchMigrationsDeleteByQuery;
}
