<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migrations_id
 * @property string $add
 * @property string $remove
 * @property string $remove_indices
 */
class ElasticsearchMigrationsAlias extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migrations_alias';
}
