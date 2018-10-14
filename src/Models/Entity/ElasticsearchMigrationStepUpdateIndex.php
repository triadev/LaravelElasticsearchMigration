<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migration_step_id
 * @property string|null $mappings
 * @property string|null $settings
 * @property bool $close_index
 */
class ElasticsearchMigrationStepUpdateIndex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration_step_update_index';
}
