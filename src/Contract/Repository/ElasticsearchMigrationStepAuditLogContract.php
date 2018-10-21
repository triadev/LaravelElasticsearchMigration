<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAuditLog;

interface ElasticsearchMigrationStepAuditLogContract
{
    /**
     * Create
     *
     * @param int $migrationStepId
     * @param int $status
     * @return ElasticsearchMigrationStepAuditLog
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationStepId,
        int $status
    ) : ElasticsearchMigrationStepAuditLog;
}
