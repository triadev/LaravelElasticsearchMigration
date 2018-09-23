<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsCreateIndexContract;

class ElasticsearchMigrationsCreateIndex implements ElasticsearchMigrationsCreateIndexContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationsId,
        array $mappings,
        ?array $settings = null
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex();
        $dbMigration->migrations_id = $migrationsId;
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
    public function find(int $migrationsId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex::where(
            'migrations_id',
            $migrationsId
        )->first();
    }
}
