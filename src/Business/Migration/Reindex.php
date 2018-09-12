<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Exception\IndexNotExist;
use Triadev\EsMigration\Models\Migration;

class Reindex
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param Migration $migration
     *
     * @throws IndexNotExist
     */
    public function migrate(Client $esClient, Migration $migration)
    {
        if ($migration->getReindex()) {
            if (!$esClient->indices()->exists(['index' => $migration->getReindex()->getIndex()])) {
                throw new IndexNotExist();
            }
        
            if ($migration->getReindex()->isRefresh()) {
                $esClient->indices()->refresh([
                    'index' => sprintf(
                        "%s,%s",
                        $migration->getIndex(),
                        $migration->getReindex()->getIndex()
                    )
                ]);
            }
        
            $esClient->reindex([
                'body' => [
                    'source' => [
                        'index' => $migration->getIndex()
                    ],
                    'dest' => [
                        'index' => $migration->getReindex()->getIndex()
                    ]
                ]
            ]);
        }
    }
}
