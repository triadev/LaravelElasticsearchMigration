<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepReindexContract;

class ElasticsearchMigrationStepReindex implements ElasticsearchMigrationStepReindexContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationStepId,
        string $destIndex,
        bool $refreshSourceIndex = false,
        array $global = [],
        array $source = [],
        array $dest = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex();
        
        $dbMigration->migration_step_id = $migrationStepId;
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
    public function find(int $migrationStepId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex::where(
            'migration_step_id',
            $migrationStepId
        )->first();
    }
}
