<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsDeleteByQueryContract;

class ElasticsearchMigrationsDeleteByQuery implements ElasticsearchMigrationsDeleteByQueryContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationsId,
        array $query,
        ?string $type = null,
        array $options = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery();
        
        $dbMigration->migrations_id = $migrationsId;
        $dbMigration->query = json_encode($query);
        $dbMigration->type = $type;
        $dbMigration->options = json_encode($options);
    
        $dbMigration->saveOrFail();
    
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(int $migrationsId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery::where(
            'migrations_id',
            $migrationsId
        )->first();
    }
}
