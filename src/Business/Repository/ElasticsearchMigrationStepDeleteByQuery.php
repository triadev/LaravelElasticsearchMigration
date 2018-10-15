<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepDeleteByQueryContract;

class ElasticsearchMigrationStepDeleteByQuery implements ElasticsearchMigrationStepDeleteByQueryContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationStepId,
        array $query,
        ?string $type = null,
        array $options = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery();
        
        $dbMigration->migration_step_id = $migrationStepId;
        $dbMigration->query = json_encode($query);
        $dbMigration->type = $type;
        $dbMigration->options = json_encode($options);
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(
        int $migrationStepId
    ): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery::where(
            'migration_step_id',
            $migrationStepId
        )->first();
    }
}
