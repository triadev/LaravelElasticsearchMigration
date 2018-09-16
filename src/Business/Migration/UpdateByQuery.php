<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Models\Migration;

class UpdateByQuery
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param Migration $migration
     */
    public function migrate(Client $esClient, Migration $migration)
    {
        if ($migration->getUpdateByQuery()) {
            $params = [
                'index' => $migration->getIndex(),
                'body' => [
                    'query' => $migration->getUpdateByQuery()['query']
                ]
            ];
            
            if ($script = array_get($migration->getUpdateByQuery(), 'script')) {
                $params['body']['script'] = $script;
            }
            
            $params = array_merge(
                $params,
                array_except($migration->getUpdateByQuery(), ['query', 'script'])
            );
            
            $esClient->updateByQuery($params);
        }
    }
}
