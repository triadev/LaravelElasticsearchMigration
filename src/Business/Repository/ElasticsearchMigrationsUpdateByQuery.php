<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateByQueryContract;

class ElasticsearchMigrationsUpdateByQuery implements ElasticsearchMigrationsUpdateByQueryContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationsId,
        array $query,
        ?string $type = null,
        ?array $script = null,
        array $options = []
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery();
        
        $dbMigration->migrations_id = $migrationsId;
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
    public function find(int $migrationsId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery::where(
            'migrations_id',
            $migrationsId
        )->first();
    }
}
