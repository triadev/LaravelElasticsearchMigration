<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

interface ElasticsearchMigrationContract
{
    /**
     * Create or update
     *
     * @param string $migration
     * @return ElasticsearchMigration
     *
     * @throws \Throwable
     */
    public function createOrUpdate(string $migration) : ElasticsearchMigration;
    
    /**
     * Find
     *
     * @param string $migration
     * @return null|ElasticsearchMigration
     */
    public function find(string $migration) : ?ElasticsearchMigration;
    
    /**
     * Delete
     *
     * @param string $migration
     * @return bool
     *
     * @throws \Throwable
     */
    public function delete(string $migration);
}
