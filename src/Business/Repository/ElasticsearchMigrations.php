<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsContract;

class ElasticsearchMigrations implements ElasticsearchMigrationsContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationId,
        string $type,
        string $index
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrations {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrations();
        $dbMigration->migration_id = $migrationId;
        $dbMigration->type = $type;
        $dbMigration->index = $index;
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(int $migrationsId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrations
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrations::where('id', $migrationsId)
            ->first();
    }
}
