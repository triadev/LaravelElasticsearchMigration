<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migrations_id
 * @property string $query
 * @property string|null $type
 * @property string|null $script
 * @property string $options
 */
class ElasticsearchMigrationsUpdateByQuery extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migrations_update_by_query';
}
