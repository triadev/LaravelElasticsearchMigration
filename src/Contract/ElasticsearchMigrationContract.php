<?php
namespace Triadev\EsMigration\Contract;

use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;

interface ElasticsearchMigrationContract
{
    /**
     * Create migration
     *
     * @param string $migration
     * @return bool
     */
    public function createMigration(string $migration) : bool;
    
    /**
     * Add migration step
     *
     * @param string $migration
     * @param string $type
     * @param array $params
     * @param int $priority
     * @return bool
     */
    public function addMigrationStep(
        string $migration,
        string $type,
        array $params = [],
        int $priority = 1
    ) : bool;
    
    /**
     * Get migration status
     *
     * @param string $migration
     * @return array [
     *      'migration' => STRING,
     *      'status' => STRING,
     *      'steps' => [
     *          'type' => STRING,
     *          'status' => INTEGER,
     *          'error' => STRING|NULL,
     *          'created_at' => DATETIME,
     *          'updated_at' => DATETIME
     *      ]
     * ]
     */
    public function getMigrationStatus(string $migration) : array;
    
    /**
     * Start migration
     *
     * @param string $migration
     * @param ElasticsearchClients $elasticsearchClients
     *
     * @throws MigrationAlreadyDone
     * @throws \Throwable
     */
    public function startMigration(string $migration, ElasticsearchClients $elasticsearchClients);
}
