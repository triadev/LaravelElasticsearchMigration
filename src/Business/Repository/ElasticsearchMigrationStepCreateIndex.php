<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepCreateIndexContract;

class ElasticsearchMigrationStepCreateIndex implements ElasticsearchMigrationStepCreateIndexContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationStepId,
        array $mappings,
        ?array $settings = null
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex();
        
        $dbMigration->migration_step_id = $migrationStepId;
        $dbMigration->mappings = json_encode($mappings);
    
        if (is_array($settings)) {
            $dbMigration->settings = json_encode($settings);
        }
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(
        int $migrationStepId
    ): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex::where(
            'migration_step_id',
            $migrationStepId
        )->first();
    }
}
