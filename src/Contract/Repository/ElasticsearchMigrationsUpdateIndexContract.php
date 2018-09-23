<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex;

interface ElasticsearchMigrationsUpdateIndexContract
{
    /**
     * Create
     *
     * @param int $migrationsId
     * @param array|null $mappings
     * @param array|null $settings
     * @param bool $closeIndex
     * @return ElasticsearchMigrationsUpdateIndex
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationsId,
        ?array $mappings = null,
        ?array $settings = null,
        bool $closeIndex = false
    ) : ElasticsearchMigrationsUpdateIndex;
    
    /**
     * Find
     *
     * @param int $migrationsId
     * @return null|ElasticsearchMigrationsUpdateIndex
     */
    public function find(int $migrationsId) : ?ElasticsearchMigrationsUpdateIndex;
}
