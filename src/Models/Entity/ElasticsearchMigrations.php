<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $migration_id
 * @property string $type
 * @property string $index
 */
class ElasticsearchMigrations extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migrations';
    
    /**
     * Get migration by type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|string(deleteIndex)|null
     */
    public function migrationByType()
    {
        switch ($this->getAttribute('type')) {
            case 'createIndex':
                return $this->hasOne(ElasticsearchMigrationsCreateIndex::class, 'migrations_id');
                break;
            case 'updateIndex':
                return $this->hasOne(ElasticsearchMigrationsUpdateIndex::class, 'migrations_id');
                break;
            case 'deleteIndex':
                return 'deleteIndex';
                break;
            case 'alias':
                return $this->hasOne(ElasticsearchMigrationsAlias::class, 'migrations_id');
                break;
            case 'deleteByQuery':
                return $this->hasOne(ElasticsearchMigrationsDeleteByQuery::class, 'migrations_id');
                break;
            case 'updateByQuery':
                return $this->hasOne(ElasticsearchMigrationsUpdateByQuery::class, 'migrations_id');
                break;
            case 'reindex':
                return $this->hasOne(ElasticsearchMigrationsReindex::class, 'migrations_id');
                break;
            default:
                break;
        }
        
        return null;
    }
}