<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Models\Migration;

class Alias
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param Migration $migration
     */
    public function migrate(Client $esClient, Migration $migration)
    {
        if ($migration->getAlias()) {
            if (!empty($migration->getAlias()->getAdd())) {
                foreach ($migration->getAlias()->getAdd() as $alias) {
                    $esClient->indices()->putAlias([
                        'index' => $migration->getIndex(),
                        'name' => $alias
                    ]);
                }
            }
    
            if (!empty($migration->getAlias()->getRemove())) {
                foreach ($migration->getAlias()->getRemove() as $alias) {
                    $esClient->indices()->deleteAlias([
                        'index' => $migration->getIndex(),
                        'name' => $alias
                    ]);
                }
            }
        }
    }
}
