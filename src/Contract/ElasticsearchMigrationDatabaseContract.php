<?php
namespace Triadev\EsMigration\Contract;

interface ElasticsearchMigrationDatabaseContract
{
    /**
     * Create migration
     *
     * @param string $migration
     * @return bool
     */
    public function createMigration(string $migration) : bool;
    
    /**
     * Add migration
     *
     * @param string $migration
     * @param string $type
     * @param string $index
     * @param array $params
     * @return bool
     */
    public function addMigration(string $migration, string $type, string $index, array $params = []) : bool;
    
    /**
     * Get migration
     *
     * @param string $migration
     * @return array
     */
    public function getMigration(string $migration) : array;
}
