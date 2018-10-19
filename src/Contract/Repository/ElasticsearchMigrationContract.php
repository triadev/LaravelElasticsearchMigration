<?php
namespace Triadev\EsMigration\Contract\Repository;

use Illuminate\Database\Eloquent\Collection;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

interface ElasticsearchMigrationContract
{
    /**
     * Create or update
     *
     * @param string $migration
     * @param int $status
     * @param string|null $error
     * @return ElasticsearchMigration
     *
     * @throws \Throwable
     */
    public function createOrUpdate(
        string $migration,
        int $status = MigrationStatus::MIGRATION_STATUS_WAIT,
        ?string $error = null
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
