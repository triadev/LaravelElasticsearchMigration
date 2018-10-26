<?php
namespace Triadev\EsMigration\Business\Repository;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Triadev\EsMigration\Business\Events\MigrationStepDone;
use Triadev\EsMigration\Business\Events\MigrationStepError;
use Triadev\EsMigration\Business\Events\MigrationStepRunning;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
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
        array $params = [],
        int $priority = 1,
        bool $stopOnFailure = true
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep();
        
        $dbMigration->migration_id = $migrationId;
        $dbMigration->type = $type;
        $dbMigration->status = MigrationStatus::MIGRATION_STATUS_WAIT;
        $dbMigration->params = json_encode($params);
        $dbMigration->priority = $priority;
        $dbMigration->stop_on_failure = $stopOnFailure;
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function update(
        int $migrationStepId,
        int $status,
        ?string $error = null,
        ?int $priority = null,
        ?bool $stopOnFailure = null
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep {
        $entity = $this->find($migrationStepId);
        if (!$entity) {
            throw new MigrationsNotExist();
        }
        
        if ((new MigrationStatus())->isMigrationStatusValid($status)) {
            $entity->status = $status;
            $entity->error = $error;
        }
        
        if (is_int($priority)) {
            $entity->priority = $priority;
        }
        
        if (is_bool($stopOnFailure)) {
            $entity->stop_on_failure = $stopOnFailure;
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
        try {
            return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep::where('id', $migrationStepId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function delete(int $migrationStepId)
    {
        if ($migrationStep = $this->find($migrationStepId)) {
            $migrationStep->delete();
        }
    }
    
    private function dispatchStatus(\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep $migration)
    {
        switch ($migration->status) {
            case MigrationStatus::MIGRATION_STATUS_RUNNING:
                $event = new MigrationStepRunning($migration);
                break;
            case MigrationStatus::MIGRATION_STATUS_DONE:
                $event = new MigrationStepDone($migration);
                break;
            case MigrationStatus::MIGRATION_STATUS_ERROR:
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
