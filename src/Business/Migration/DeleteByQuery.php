<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Models\Migration;

class DeleteByQuery
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param Migration $migration
     */
    public function migrate(Client $esClient, Migration $migration)
    {
        if ($migration->getDeleteByQuery()) {
            $params = [
                'index' => $migration->getIndex(),
                'body' => [
                    'query' => $migration->getDeleteByQuery()['query']
                ]
            ];
            
            $params = array_merge(
                $params,
                array_except($migration->getDeleteByQuery(), ['query'])
            );
            
            $esClient->deleteByQuery($params);
        }
    }
}
