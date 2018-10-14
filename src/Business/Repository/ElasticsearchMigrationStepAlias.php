<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepAliasContract;

class ElasticsearchMigrationStepAlias implements ElasticsearchMigrationStepAliasContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationStepId,
        array $add = [],
        array $remove = [],
        array $removeIndices = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias();
        
        $dbMigration->migration_step_id = $migrationStepId;
        $dbMigration->add = json_encode($add);
        $dbMigration->remove = json_encode($remove);
        $dbMigration->remove_indices = json_encode($removeIndices);
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(int $migrationStepId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias::where(
            'migration_step_id',
            $migrationStepId
        )->first();
    }
}
