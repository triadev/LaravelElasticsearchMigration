<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migrations_id
 * @property string|null $mappings
 * @property string|null $settings
 * @property bool $close_index
 */
class ElasticsearchMigrationsUpdateIndex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migrations_update_index';
}
