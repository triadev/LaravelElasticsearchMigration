<?php
namespace Triadev\EsMigration\Contract;

interface ElasticsearchMigrationDatabaseContract
{
    const MIGRATION_TYPE_CREATE_INDEX = 'createIndex';
    const MIGRATION_TYPE_UPDATE_INDEX = 'updateIndex';
    const MIGRATION_TYPE_DELETE_INDEX = 'deleteIndex';
    const MIGRATION_TYPE_ALIAS = 'alias';
    const MIGRATION_TYPE_DELETE_BY_QUERY = 'deleteByQuery';
    const MIGRATION_TYPE_UPDATE_BY_QUERY = 'updateByQuery';
    const MIGRATION_TYPE_REINDEX = 'reindex';
    
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
