<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationAuditLogContract;

class ElasticsearchMigrationAuditLog implements ElasticsearchMigrationAuditLogContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationId,
        int $status
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationAuditLog {
        $entity = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationAuditLog();
        
        $entity->migration_id = $migrationId;
        $entity->status = $status;
        
        $entity->saveOrFail();
        
        return $entity;
    }
}
