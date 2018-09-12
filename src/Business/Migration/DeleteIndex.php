<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Models\Migration;

class DeleteIndex
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param Migration $migration
     */
    public function migrate(Client $esClient, Migration $migration)
    {
        if ($migration->getType() == 'delete') {
            $esClient->indices()->delete([
                'index' => $migration->getIndex()
            ]);
        }
    }
}
