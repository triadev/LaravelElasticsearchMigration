<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $migration_id
 * @property string $type
 * @property integer $status
 * @property string|null $error
 * @property string $params
 * @property integer $priority
 * @property bool $stop_on_failure
 * @property string $created_at
 * @property string $updated_at
 */
class ElasticsearchMigrationStep extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration_step';
}
