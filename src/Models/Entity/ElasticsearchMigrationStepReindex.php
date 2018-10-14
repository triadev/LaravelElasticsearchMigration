<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migration_step_id
 * @property string $dest_index
 * @property bool $refresh_source_index
 * @property string $global
 * @property string $source
 * @property string $dest
 */
class ElasticsearchMigrationStepReindex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration_step_reindex';
}
