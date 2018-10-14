<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

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
            case 'createIndex':
                return $this->hasOne(ElasticsearchMigrationStepCreateIndex::class, 'migration_step_id');
                break;
            case 'updateIndex':
                return $this->hasOne(ElasticsearchMigrationStepUpdateIndex::class, 'migration_step_id');
                break;
            case 'deleteIndex':
                return 'deleteIndex';
                break;
            case 'alias':
                return $this->hasOne(ElasticsearchMigrationStepAlias::class, 'migration_step_id');
                break;
            case 'deleteByQuery':
                return $this->hasOne(ElasticsearchMigrationStepDeleteByQuery::class, 'migration_step_id');
                break;
            case 'updateByQuery':
                return $this->hasOne(ElasticsearchMigrationStepUpdateByQuery::class, 'migration_step_id');
                break;
            case 'reindex':
                return $this->hasOne(ElasticsearchMigrationStepReindex::class, 'migration_step_id');
                break;
            default:
                break;
        }
        
        return null;
    }
}
