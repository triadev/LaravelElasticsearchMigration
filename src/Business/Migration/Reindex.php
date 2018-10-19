<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class Reindex extends AbstractMigration
{
    /**
     * Get validation rules
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'body' => 'required|array',
            'body.source.index' => 'required|string',
            'body.dest.index' => 'required|string'
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
        $indices = implode(',', [
            array_get($params, 'body.source.index'),
            array_get($params, 'body.dest.index')
        ]);
        
        if (!$esClient->indices()->exists(['index' => $indices])) {
            throw new \Exception(sprintf("Index not exist: %s", $indices));
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
        $esClient->reindex($params);
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
