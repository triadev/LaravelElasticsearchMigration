<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $migrations_id
 * @property string $dest_index
 * @property bool $refresh_source_index
 * @property string $global
 * @property string $source
 * @property string $dest
 */
class ElasticsearchMigrationsReindex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migrations_reindex';
}
