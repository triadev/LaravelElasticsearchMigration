<?php
namespace Triadev\EsMigration\Contract\Repository;

use Illuminate\Database\Eloquent\Collection;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStatus;

interface ElasticsearchMigrationStatusContract
{
    /**
     * Create or update
     *
     * @param string $migration
     * @param string $status
     * @return ElasticsearchMigrationStatus
     *
     * @throws \Throwable
     */
    public function createOrUpdate(string $migration, string $status) : ElasticsearchMigrationStatus;
    
    /**
     * Find
     *
     * @param string $migration
     * @return null|ElasticsearchMigrationStatus
     */
    public function find(string $migration) : ?ElasticsearchMigrationStatus;
    
    /**
     * All
     *
     * @param array $fields
     * @return Collection
     */
    public function all(array $fields = ['*']) : Collection;
    
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
