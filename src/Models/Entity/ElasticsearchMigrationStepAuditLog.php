<?php
namespace Triadev\EsMigration\Models\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $migration_step_id
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 */
class ElasticsearchMigrationStepAuditLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'triadev_elasticsearch_migration_step_audit_log';
}
