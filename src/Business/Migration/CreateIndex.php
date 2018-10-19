<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class CreateIndex extends AbstractMigration
{
    /**
     * Get validation rules
     *
     * @return array
     */
    public function getValidationRules() : array
    {
        return [
            'index' => 'required|string',
            'body' => 'required|array',
            'body.mappings' => 'array',
            'body.settings' => 'array'
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
        $index = array_get($params, 'index');
        
        if ($esClient->indices()->exists(['index' => $index])) {
            throw new \Exception(sprintf("Index already exist: %s", $index));
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
        $esClient->indices()->create($params);
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
        $index = array_get($params, 'index');
    
        if (!$esClient->indices()->exists(['index' => $index])) {
            throw new \Exception(sprintf("Index not exist: %s", $index));
        };
    }
}
