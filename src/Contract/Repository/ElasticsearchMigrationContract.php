<?php
namespace Triadev\EsMigration\Contract\Repository;

use Illuminate\Database\Eloquent\Collection;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

interface ElasticsearchMigrationContract
{
    const ELASTICSEARCH_MIGRATION_STATUS_WAIT = 0;
    const ELASTICSEARCH_MIGRATION_STATUS_RUNNING = 1;
    const ELASTICSEARCH_MIGRATION_STATUS_DONE = 2;
    const ELASTICSEARCH_MIGRATION_STATUS_ERROR = 3;
    
    /**
     * Create or update
     *
     * @param string $migration
     * @param int $status
     * @return ElasticsearchMigration
     *
     * @throws \Throwable
     */
    public function createOrUpdate(
        string $migration,
        int $status = self::ELASTICSEARCH_MIGRATION_STATUS_WAIT
    ) : ElasticsearchMigration;
    
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
    
    /**
     * All
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']) : Collection;
}
