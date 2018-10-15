<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;

/**
 * @property integer $id
 * @property integer $migration_id
 * @property string $type
 * @property string $index
 * @property integer $status
 * @property string|null $error
 */
class ElasticsearchMigrationStep extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration_step';
    
    /**
     * Get migration by type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|string(deleteIndex)|null
     */
    public function migrationByType()
    {
        switch ($this->getAttribute('type')) {
            case ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_CREATE_INDEX:
                return $this->hasOne(ElasticsearchMigrationStepCreateIndex::class, 'migration_step_id');
                break;
            case ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_UPDATE_INDEX:
                return $this->hasOne(ElasticsearchMigrationStepUpdateIndex::class, 'migration_step_id');
                break;
            case ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_DELETE_INDEX:
                return ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_DELETE_INDEX;
                break;
            case ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_ALIAS:
                return $this->hasOne(ElasticsearchMigrationStepAlias::class, 'migration_step_id');
                break;
            case ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_DELETE_BY_QUERY:
                return $this->hasOne(ElasticsearchMigrationStepDeleteByQuery::class, 'migration_step_id');
                break;
            case ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_UPDATE_BY_QUERY:
                return $this->hasOne(ElasticsearchMigrationStepUpdateByQuery::class, 'migration_step_id');
                break;
            case ElasticsearchMigrationDatabaseContract::MIGRATION_TYPE_REINDEX:
                return $this->hasOne(ElasticsearchMigrationStepReindex::class, 'migration_step_id');
                break;
            default:
                break;
        }
        
        return null;
    }
}
