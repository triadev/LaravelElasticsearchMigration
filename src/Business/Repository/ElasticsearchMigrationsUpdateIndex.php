<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateIndexContract;

class ElasticsearchMigrationsUpdateIndex implements ElasticsearchMigrationsUpdateIndexContract
{
    /**
     * @inheritdoc
     */
    public function create(
        int $migrationsId,
        ?array $mappings = null,
        ?array $settings = null,
        bool $closeIndex = false
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex {
        $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex();
        
        $dbMigration->migrations_id = $migrationsId;
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
    public function find(int $migrationsId): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex::where(
            'migrations_id',
            $migrationsId
        )->first();
    }
}
