<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class UpdateByQuery
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param \Triadev\EsMigration\Models\Migrations\UpdateByQuery $migration
     */
    public function migrate(Client $esClient, \Triadev\EsMigration\Models\Migrations\UpdateByQuery $migration)
    {
        $params = [
            'index' => $migration->getIndex(),
            'body' => [
                'query' => $migration->getQuery()
            ]
        ];
    
        if ($migration->getType()) {
            $params['type'] = $migration->getType();
        }
        
        if ($migration->getScript()) {
            $params['body']['script'] = $migration->getScript();
        }
    
        $params = array_merge(
            $params,
            $migration->getOptions()
        );
    
        $esClient->updateByQuery($params);
    }
}
