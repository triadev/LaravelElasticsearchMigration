<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class UpdateByQuery extends AbstractMigration
{
    /**
     * Get validation rules
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'index' => 'required|string'
        ];
    }
    
    /**
     * Pre check
     *
     * @param Client $esClient
     * @param array $params
     *
     * @throws \Exception
     */
    public function preCheck(Client $esClient, array $params)
    {
        // TODO: Implement preCheck() method.
    }
    
    /**
     * Start migration
     *
     * @param Client $esClient
     * @param array $params
     */
    public function startMigration(Client $esClient, array $params)
    {
        $esClient->updateByQuery($params);
    }
    
    /**
     * Post check
     *
     * @param Client $esClient
     * @param array $params
     *
     * @throws \Exception
     */
    public function postCheck(Client $esClient, array $params)
    {
        // TODO: Implement postCheck() method.
    }
}
