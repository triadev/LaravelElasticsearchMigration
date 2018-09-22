<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Exception\IndexNotExist;

class Reindex
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param \Triadev\EsMigration\Models\Migrations\Reindex $migration
     *
     * @throws IndexNotExist
     */
    public function migrate(Client $esClient, \Triadev\EsMigration\Models\Migrations\Reindex $migration)
    {
        if (!$esClient->indices()->exists(['index' => $migration->getDestIndex()])) {
            throw new IndexNotExist();
        }
    
        if ($migration->isRefreshSourceIndex()) {
            $esClient->indices()->refresh([
                'index' => $migration->getIndex()
            ]);
        }
    
        $body = [
            'source' => [
                'index' => $migration->getIndex()
            ],
            'dest' => [
                'index' => $migration->getDestIndex()
            ]
        ];
    
        $body = array_merge($body, $migration->getGlobal());
        $body['source'] = array_merge($body['source'], $migration->getSource());
        $body['dest'] = array_merge($body['dest'], $migration->getDest());
    
        $esClient->reindex([
            'body' => $body
        ]);
    }
}
