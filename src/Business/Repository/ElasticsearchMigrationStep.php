<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Business\Events\MigrationStepDone;
use Triadev\EsMigration\Business\Events\MigrationStepError;
use Triadev\EsMigration\Business\Events\MigrationStepRunning;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Exception\MigrationsNotExist;

class ElasticsearchMigrationStep implements ElasticsearchMigrationStepContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationId,
        string $type,
        string $index
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep();
        
        $dbMigration->migration_id = $migrationId;
        $dbMigration->type = $type;
        $dbMigration->index = $index;
        $dbMigration->status = self::ELASTICSEARCH_MIGRATION_STEP_STATUS_WAIT;
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function update(
        int $migrationStepId,
        int $status,
        ?string $error = null
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep {
        $entity = $this->find($migrationStepId);
        if (!$entity) {
            throw new MigrationsNotExist();
        }
        
        if ($this->isStatusValid($status)) {
            $entity->status = $status;
            $entity->error = $error;
        }
        
        $entity->saveOrFail();
        
        $this->dispatchStatus($entity);
        
        return $entity;
    }
    
    /**
     * @inheritdoc
     */
    public function find(int $migrationStepId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep::where('id', $migrationStepId)
            ->first();
    }
    
    private function isStatusValid(int $status) : bool
    {
        $valid = [
            self::ELASTICSEARCH_MIGRATION_STEP_STATUS_WAIT,
            self::ELASTICSEARCH_MIGRATION_STEP_STATUS_RUNNING,
            self::ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE,
            self::ELASTICSEARCH_MIGRATION_STEP_STATUS_ERROR
        ];
        
        if (in_array($status, $valid)) {
            return true;
        }
        
        return false;
    }
    
    private function dispatchStatus(\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep $migration)
    {
        switch ($migration->status) {
            case self::ELASTICSEARCH_MIGRATION_STEP_STATUS_RUNNING:
                $event = new MigrationStepRunning($migration);
                break;
            case self::ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE:
                $event = new MigrationStepDone($migration);
                break;
            case self::ELASTICSEARCH_MIGRATION_STEP_STATUS_ERROR:
                $event = new MigrationStepError($migration);
                break;
            default:
                $event = null;
                break;
        }
        
        if ($event) {
            event($event);
        }
    }
}
