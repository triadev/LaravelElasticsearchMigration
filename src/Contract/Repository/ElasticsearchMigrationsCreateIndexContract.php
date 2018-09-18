<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex;

interface ElasticsearchMigrationsCreateIndexContract
{
    /**
     * Create
     *
     * @param int $migrationsId
     * @param array $mappings
     * @param array|null $settings
     * @return ElasticsearchMigrationsCreateIndex
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationsId,
        array $mappings,
        ?array $settings = null
    ) : ElasticsearchMigrationsCreateIndex;
    
    /**
     * Find
     *
     * @param int $migrationsId
     * @return null|ElasticsearchMigrationsCreateIndex
     */
    public function find(int $migrationsId) : ?ElasticsearchMigrationsCreateIndex;
}
