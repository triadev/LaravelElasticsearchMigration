<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationAuditLog;

interface ElasticsearchMigrationAuditLogContract
{
    /**
     * Create
     *
     * @param int $migrationId
     * @param int $status
     * @return ElasticsearchMigrationAuditLog
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationId,
        int $status
    ) : ElasticsearchMigrationAuditLog;
}
