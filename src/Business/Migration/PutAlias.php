<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class PutAlias extends AbstractMigration
{
    /**
     * Get validation rules
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'index' => 'required|string',
            'name' => 'required|string'
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
        if (!$esClient->indices()->exists(['index' => $index])) {
            throw new \Exception(sprintf("Index not exist: %s", $index));
        }
        
        if ($esClient->indices()->existsAlias($params)) {
            throw new \Exception(sprintf("Alias already exist: %s", $params['name']));
        };
    }
    
    /**
     * Start migration
     *
     * @param Client $esClient
     * @param array $params
     */
    public function startMigration(Client $esClient, array $params)
    {
        $esClient->indices()->putAlias($params);
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
        if (!$esClient->indices()->existsAlias($params)) {
            throw new \Exception(sprintf("Alias not exist: %s", $params['name']));
        };
    }
}
