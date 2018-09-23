<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

class ElasticsearchMigrationsReindex extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migrations_reindex';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'migration_id',
        'dest_index',
        'refresh_source_index',
        'global',
        'source',
        'dest'
    ];
}
