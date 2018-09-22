<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class DeleteIndex
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param \Triadev\EsMigration\Models\Migrations\DeleteIndex $migration
     */
    public function migrate(Client $esClient, \Triadev\EsMigration\Models\Migrations\DeleteIndex $migration)
    {
        $esClient->indices()->delete([
            'index' => $migration->getIndex()
        ]);
    }
}
