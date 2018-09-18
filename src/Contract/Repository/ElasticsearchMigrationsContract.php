<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrations;

interface ElasticsearchMigrationsContract
{
    /**
     * Create
     *
     * @param int $migrationId
     * @param string $type
     * @param string $index
     * @return ElasticsearchMigrations
     *
     * @throws \Throwable
     */
    public function create(int $migrationId, string $type, string $index) : ElasticsearchMigrations;
    
    /**
     * Find
     *
     * @param int $migrationsId
     * @return null|ElasticsearchMigrations
     */
    public function find(int $migrationsId) : ?ElasticsearchMigrations;
}
