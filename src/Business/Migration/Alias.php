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
            $alias = $migration->getAlias();
            
            if (!empty($alias->getAdd())) {
                foreach ($alias->getAdd() as $a) {
                    $esClient->indices()->putAlias([
                        'index' => $migration->getIndex(),
                        'name' => $a
                    ]);
                }
            }
    
            if (!empty($alias->getRemove())) {
                foreach ($alias->getRemove() as $a) {
                    $esClient->indices()->deleteAlias([
                        'index' => $migration->getIndex(),
                        'name' => $a
                    ]);
                }
            }
    
            if (!empty($alias->getRemoveIndex())) {
                foreach ($alias->getRemoveIndex() as $i) {
                    $esClient->indices()->delete([
                        'index' => $i
                    ]);
                }
            }
        }
    }
}
