<?php
namespace Triadev\EsMigration\Business\Repository;

use Illuminate\Database\Eloquent\Collection;
use Triadev\EsMigration\Business\Events\MigrationDone;
use Triadev\EsMigration\Business\Events\MigrationError;
use Triadev\EsMigration\Business\Events\MigrationRunning;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /**
     * @inheritdoc
     */
    public function createOrUpdate(
        string $migration,
        int $status = self::ELASTICSEARCH_MIGRATION_STATUS_WAIT,
        ?string $error = null
    ): \Triadev\EsMigration\Models\Entity\ElasticsearchMigration {
        $dbMigration = $this->find($migration);
        
        if (!$dbMigration) {
            $dbMigration = new \Triadev\EsMigration\Models\Entity\ElasticsearchMigration();
            $dbMigration->migration = $migration;
        }
        
        if ($this->isStatusValid($status)) {
            $dbMigration->status = $status;
            $dbMigration->error = $error;
        }
        
        $dbMigration->saveOrFail();
        
        $this->dispatchStatus($dbMigration);
        
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
    
    private function dispatchStatus(\Triadev\EsMigration\Models\Entity\ElasticsearchMigration $migration)
    {
        switch ($migration->status) {
            case self::ELASTICSEARCH_MIGRATION_STATUS_RUNNING:
                $event = new MigrationRunning($migration);
                break;
            case self::ELASTICSEARCH_MIGRATION_STATUS_DONE:
                $event = new MigrationDone($migration);
                break;
            case self::ELASTICSEARCH_MIGRATION_STATUS_ERROR:
                $event = new MigrationError($migration);
                break;
            default:
                $event = null;
                break;
        }
        
        if ($event) {
            event($event);
        }
    }
}
