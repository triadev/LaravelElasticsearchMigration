<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class DeleteIndex extends AbstractMigration
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
        $index = $params['index'];
    
        if (!$esClient->indices()->exists(['index' => $params['index']])) {
            throw new \Exception(sprintf("Index not exist: %s", $index));
        }
    }
    
    /**
     * Start migration
     *
     * @param Client $esClient
     * @param array $params
     */
    public function startMigration(Client $esClient, array $params)
    {
        $esClient->indices()->delete($params);
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
        $index = $params['index'];
    
        if ($esClient->indices()->exists(['index' => $params['index']])) {
            throw new \Exception(sprintf("Index exist: %s", $index));
        }
    }
}
