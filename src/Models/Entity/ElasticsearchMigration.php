<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $migration
 * @property string $status
 * @property string|null $error
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
     * Get migration steps
     *
     * @return HasMany
     */
    public function migrationSteps() : HasMany
    {
        return $this->hasMany(ElasticsearchMigrationStep::class, 'migration_id');
    }
}
