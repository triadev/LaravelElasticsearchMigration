<?php
namespace Triadev\EsMigration\Business\Events;

use Illuminate\Queue\SerializesModels;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;

class MigrationStepError
{
    use SerializesModels;
    
    /** @var ElasticsearchMigrationStep */
    private $migrationStep;
    
    /**
     * MigrationStepDone constructor.
     * @param ElasticsearchMigrationStep $migrationStep
     */
    public function __construct(ElasticsearchMigrationStep $migrationStep)
    {
        $this->migrationStep = $migrationStep;
    }
    
    /**
     * @return ElasticsearchMigrationStep
     */
    public function getMigrationStep(): ElasticsearchMigrationStep
    {
        return $this->migrationStep;
    }
}
