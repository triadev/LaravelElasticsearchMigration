<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class DeleteByQuery
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param \Triadev\EsMigration\Models\Migrations\DeleteByQuery $migration
     */
    public function migrate(Client $esClient, \Triadev\EsMigration\Models\Migrations\DeleteByQuery $migration)
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
    
        $params = array_merge(
            $params,
            $migration->getOptions()
        );
    
        $esClient->deleteByQuery($params);
    }
}
