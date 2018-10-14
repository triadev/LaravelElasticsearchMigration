<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migration_step_id
 * @property string $query
 * @property string|null $type
 * @property string $options
 */
class ElasticsearchMigrationStepDeleteByQuery extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration_step_delete_by_query';
}
