<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Illuminate\Support\Facades\Log;
use Triadev\EsMigration\Exception\MigrationStepValidation;
use Triadev\EsMigration\Models\MigrationStep;
use Illuminate\Support\Facades\Validator;

abstract class AbstractMigration
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param MigrationStep $migrationStep
     *
     * @throws MigrationStepValidation
     * @throws \Exception
     */
    public function migrate(Client $esClient, MigrationStep $migrationStep)
    {
        $params = $migrationStep->getParams();
        
        $validator = Validator::make($params, $this->getValidationRules());
        
        if ($validator->fails()) {
            Log::error("The migration step validation failed.", [
                'id' => $migrationStep->getId(),
                'type' => $migrationStep->getType()
            ]);
            
            throw new MigrationStepValidation(sprintf(
                "The migration step validation failed: %s",
                $migrationStep->getId()
            ));
        }
        
        $this->preCheck($esClient, $params);
        
        $this->startMigration($esClient, $params);
        
        $this->postCheck($esClient, $params);
    }
    
    /**
     * Get validation rules
     *
     * @return array
     */
    abstract public function getValidationRules() : array;
    
    /**
     * Pre check
     *
     * @param Client $esClient
     * @param array $params
     *
     * @throws \Exception
     */
    abstract public function preCheck(Client $esClient, array $params);
    
    /**
     * Start migration
     *
     * @param Client $esClient
     * @param array $params
     */
    abstract public function startMigration(Client $esClient, array $params);
    
    /**
     * Post check
     *
     * @param Client $esClient
     * @param array $params
     *
     * @throws \Exception
     */
    abstract public function postCheck(Client $esClient, array $params);
}
