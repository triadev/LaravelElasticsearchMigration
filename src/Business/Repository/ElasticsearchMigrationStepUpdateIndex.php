<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateIndexContract;

class ElasticsearchMigrationStepUpdateIndex implements ElasticsearchMigrationStepUpdateIndexContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationStepId,
        ?array $mappings = null,
        ?array $settings = null,
        bool $closeIndex = false
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex();
        
        $dbMigration->migration_step_id = $migrationStepId;
        $dbMigration->close_index = $closeIndex;
    
        if (is_array($mappings)) {
            $dbMigration->mappings = json_encode($mappings);
        }
    
        if (is_array($settings)) {
            $dbMigration->settings = json_encode($settings);
        }
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(int $migrationStepId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex::where(
            'migration_step_id',
            $migrationStepId
        )->first();
    }
}
