<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $migration
 * @property string $status
 */
class ElasticsearchMigration extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration';
    
    /**
     * Get migrations
     *
     * @return HasMany
     */
    public function migrations() : HasMany
    {
        return $this->hasMany(ElasticsearchMigrations::class, 'migration_id');
    }
}
