<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migration_step_id
 * @property string $add
 * @property string $remove
 * @property string $remove_indices
 */
class ElasticsearchMigrationStepAlias extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration_step_alias';
}
