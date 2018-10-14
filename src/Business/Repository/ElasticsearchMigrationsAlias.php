<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsAliasContract;

class ElasticsearchMigrationsAlias implements ElasticsearchMigrationsAliasContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationsId,
        array $add = [],
        array $remove = [],
        array $removeIndices = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias();
        
        $dbMigration->migrations_id = $migrationsId;
        $dbMigration->add = json_encode($add);
        $dbMigration->remove = json_encode($remove);
        $dbMigration->remove_indices = json_encode($removeIndices);
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(int $migrationsId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias::where(
            'migrations_id',
            $migrationsId
        )->first();
    }
}
