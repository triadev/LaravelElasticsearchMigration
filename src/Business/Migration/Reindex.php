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
        if ($reindex = $migration->getReindex()) {
            if (!$esClient->indices()->exists(['index' => $reindex->getIndex()])) {
                throw new IndexNotExist();
            }
        
            if ($reindex->isRefresh()) {
                $esClient->indices()->refresh([
                    'index' => sprintf(
                        "%s,%s",
                        $migration->getIndex(),
                        $migration->getReindex()->getIndex()
                    )
                ]);
            }
            
            $body = [
                'source' => [
                    'index' => $migration->getIndex()
                ],
                'dest' => [
                    'index' => $migration->getReindex()->getIndex()
                ]
            ];
            
            if ($versionType = $reindex->getVersionType()) {
                $body['dest']['version_type'] = $versionType;
            }
        
            $esClient->reindex([
                'body' => $body
            ]);
        }
    }
}
