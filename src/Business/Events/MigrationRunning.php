<?php
namespace Triadev\EsMigration\Business\Events;

use Illuminate\Queue\SerializesModels;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

class MigrationRunning
{
    use SerializesModels;
    
    /** @var ElasticsearchMigration */
    private $migration;
    
    /**
     * MigrationRunning constructor.
     * @param ElasticsearchMigration $migration
     */
    public function __construct(ElasticsearchMigration $migration)
    {
        $this->migration = $migration;
    }
    
    /**
     * @return ElasticsearchMigration
     */
    public function getMigration(): ElasticsearchMigration
    {
        return $this->migration;
    }
}
