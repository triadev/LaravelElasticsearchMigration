<?php
namespace Triadev\EsMigration\Contract;

use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;
use Triadev\EsMigration\Exception\MigrationStepNotFound;

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
     * Delete migration
     *
     * @param string $migration
     * @return bool
     */
    public function deleteMigration(string $migration) : bool;
    
    /**
     * Add migration step
     *
     * @param string $migration
     * @param string $type
     * @param array $params
     * @param int $priority
     * @param bool $stopOnFailure
     * @return bool
     */
    public function addMigrationStep(
        string $migration,
        string $type,
        array $params = [],
        int $priority = 1,
        bool $stopOnFailure = true
    ) : bool;
    
    /**
     * Delete migration step
     *
     * @param int $migrationStepId
     * @return bool
     */
    public function deleteMigrationStep(int $migrationStepId) : bool;
    
    /**
     * Start single migration step
     *
     * @param int $migrationStepId
     * @param ElasticsearchClients $elasticsearchClients
     *
     * @throws MigrationStepNotFound
     * @throws \Throwable
     */
    public function startSingleMigrationStep(int $migrationStepId, ElasticsearchClients $elasticsearchClients);
    
    /**
     * Get migration status
     *
     * @param string $migration
     * @return array [
     *      'migration' => STRING,
     *      'status' => STRING,
     *      'error' => STRING|NULL,
     *      'steps' => [
     *          'id' => INTEGER,
     *          'type' => STRING,
     *          'status' => INTEGER,
     *          'error' => STRING|NULL,
     *          'params' => ARRAY,
     *          'priority' => INTEGER,
     *          'stop_on_failure' => BOOLEAN,
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
    
    /**
     * Restart migration
     *
     * @param string $migration
     * @param ElasticsearchClients $elasticsearchClients
     *
     * @throws MigrationAlreadyDone
     * @throws \Throwable
     */
    public function restartMigration(string $migration, ElasticsearchClients $elasticsearchClients);
}
