<?php
namespace Triadev\EsMigration\Business\Repository;

use Illuminate\Database\Eloquent\Collection;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract;

class ElasticsearchMigrationStatus implements ElasticsearchMigrationStatusContract
{
    /**
     * @inheritdoc
     */
    public function createOrUpdate(
        string $migration,
        string $status
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStatus {
        $dbMigration = $this->find($migration);
        
        if (!$dbMigration) {
            $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStatus();
            $dbMigration->migration = $migration;
        }
        
        $dbMigration->status = $status;
        $dbMigration->saveOrFail();
        
        return $dbMigration;
    }
    
    /**
     * @inheritdoc
     */
    public function find(string $migration): ?\Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStatus
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStatus::where('migration', $migration)
            ->orderBy('created_at', 'desc')
            ->first();
    }
    
    /**
     * @inheritdoc
     */
    public function all(array $fields = ['*']) : Collection
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStatus::all($fields);
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
