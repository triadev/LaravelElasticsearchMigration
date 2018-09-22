<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class Alias
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param \Triadev\EsMigration\Models\Migrations\Alias $migration
     */
    public function migrate(Client $esClient, \Triadev\EsMigration\Models\Migrations\Alias $migration)
    {
        if (!empty($migration->getAdd())) {
            foreach ($migration->getAdd() as $a) {
                $esClient->indices()->putAlias([
                    'index' => $migration->getIndex(),
                    'name' => $a
                ]);
            }
        }
    
        if (!empty($migration->getRemove())) {
            foreach ($migration->getRemove() as $a) {
                $esClient->indices()->deleteAlias([
                    'index' => $migration->getIndex(),
                    'name' => $a
                ]);
            }
        }
    
        if (!empty($migration->getRemoveIndices())) {
            $esClient->indices()->delete([
                'index' => implode(',', $migration->getRemoveIndices())
            ]);
        }
    }
}
