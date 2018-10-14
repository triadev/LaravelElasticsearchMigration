<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsReindexContract;

class ElasticsearchMigrationsReindex implements ElasticsearchMigrationsReindexContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationsId,
        string $destIndex,
        bool $refreshSourceIndex = false,
        array $global = [],
        array $source = [],
        array $dest = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex();
        
        $dbMigration->migrations_id = $migrationsId;
        $dbMigration->dest_index = $destIndex;
        $dbMigration->refresh_source_index = $refreshSourceIndex;
        $dbMigration->global = json_encode($global);
        $dbMigration->source = json_encode($source);
        $dbMigration->dest = json_encode($dest);
        
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(int $migrationsId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex::where(
            'migrations_id',
            $migrationsId
        )->first();
    }
}
