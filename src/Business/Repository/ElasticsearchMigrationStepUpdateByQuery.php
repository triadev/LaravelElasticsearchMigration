<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateByQueryContract;

class ElasticsearchMigrationStepUpdateByQuery implements ElasticsearchMigrationStepUpdateByQueryContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationStepId,
        array $query,
        ?string $type = null,
        ?array $script = null,
        array $options = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery();
        
        $dbMigration->migration_step_id = $migrationStepId;
        $dbMigration->query = json_encode($query);
        $dbMigration->type = $type;
        $dbMigration->script = is_array($script) ? json_encode($script): null;
        $dbMigration->options = json_encode($options);
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * F@inheritdoc
     */
    public function find(int $migrationStepId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery::where(
            'migration_step_id',
            $migrationStepId
        )->first();
    }
}
