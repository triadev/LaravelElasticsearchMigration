<?php
namespace Triadev\EsMigration\Business\Repository;

use Illuminate\Database\Eloquent\Collection;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /**
     * @inheritdoc
     */
    public function createOrUpdate(
        string $migration,
        int $status = self::ELASTICSEARCH_MIGRATION_STATUS_WAIT
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigration {
        $dbMigration = $this->find($migration);
        
        if (!$dbMigration) {
            $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigration();
            $dbMigration->migration = $migration;
        }
        
        if ($this->isStatusValid($status)) {
            $dbMigration->status = $status;
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
    
    /**
     * @inheritdoc
     */
    public function all(array $columns = ['*']): Collection
    {
        return \Triadev\EsMigration\Models\Entity\ElasticsearchMigration::all($columns);
    }
    
    private function isStatusValid(int $status) : bool
    {
        $valid = [
            self::ELASTICSEARCH_MIGRATION_STATUS_WAIT,
            self::ELASTICSEARCH_MIGRATION_STATUS_RUNNING,
            self::ELASTICSEARCH_MIGRATION_STATUS_DONE,
            self::ELASTICSEARCH_MIGRATION_STATUS_ERROR
        ];
        
        if (in_array($status, $valid)) {
            return true;
        }
        
        return false;
    }
}
