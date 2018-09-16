<?php
namespace Triadev\EsMigration\Contract\Repository;

use Illuminate\Database\Eloquent\Collection;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

interface ElasticsearchMigrationContract
{
    /**
     * Create or update
     *
     * @param string $migration
     * @param string $status
     * @return ElasticsearchMigration
     *
     * @throws \Throwable
     */
    public function createOrUpdate(string $migration, string $status) : ElasticsearchMigration;
    
    /**
     * Find
     *
     * @param string $migration
     * @return null|ElasticsearchMigration
     */
    public function find(string $migration) : ?ElasticsearchMigration;
    
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
