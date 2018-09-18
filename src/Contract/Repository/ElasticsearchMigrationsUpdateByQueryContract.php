<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery;

interface ElasticsearchMigrationsUpdateByQueryContract
{
    /**
     * Create
     *
     * @param int $migrationsId
     * @param array $query
     * @param null|string $type
     * @param array|null $script
     * @param array $options
     * @return ElasticsearchMigrationsUpdateByQuery
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationsId,
        array $query,
        ?string $type = null,
        ?array $script = null,
        array $options = []
    ) : ElasticsearchMigrationsUpdateByQuery;
    
    /**
     * Find
     *
     * @param int $migrationsId
     * @return null|ElasticsearchMigrationsUpdateByQuery
     */
    public function find(int $migrationsId) : ?ElasticsearchMigrationsUpdateByQuery;
}
