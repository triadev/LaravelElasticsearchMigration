<?php
namespace Triadev\EsMigration\Business\Repository;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /**
     * @inheritdoc
     */
    public function createOrUpdate(
        string $migration
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigration {
        $dbMigration = $this->find($migration);
        
        if (!$dbMigration) {
            $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigration();
            $dbMigration->migration = $migration;
        }
        
        $dbMigration->saveOrFail();
        
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(string $migration): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigration
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigration::where('migration', $migration)
            ->orderBy('created_at', 'desc')
            ->first();
    }
    
    /**
     * @inheritdoc
     */
    public function delete(string $migration)
    {
        if ($migration = $this->find($migration)) {
            $migration->delete();
        }
    }
}
