<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepAuditLogContract;

class ElasticsearchMigrationStepAuditLog implements ElasticsearchMigrationStepAuditLogContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationStepId,
        int $status
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAuditLog {
        $entity = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAuditLog();
        
        $entity->migration_step_id = $migrationStepId;
        $entity->status = $status;
        
        $entity->saveOrFail();
        
        return $entity;
    }
}
