<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migrations_id
 * @property string $mappings
 * @property string $settings
 */
class ElasticsearchMigrationsCreateIndex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migrations_create_index';
}
