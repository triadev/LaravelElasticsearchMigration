<?php
namespace Triadev\EsMigration\Business\Events;

use Illuminate\Queue\SerializesModels;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

class MigrationError
{
    use SerializesModels;
    
    /** @var ElasticsearchMigration */
    private $migration;
    
    /**
     * MigrationError constructor.
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
