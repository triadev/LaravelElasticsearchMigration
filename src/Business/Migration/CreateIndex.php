<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Models\Migration;

class CreateIndex
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param Migration $migration
     */
    public function migrate(Client $esClient, Migration $migration)
    {
        if ($migration->getType() == 'create') {
            $body = [
                'mappings' => $migration->getMappings()
            ];
    
            if ($migration->getSettings()) {
                $body['settings'] = $migration->getSettings();
            }
    
            $esClient->indices()->create([
                'index' => $migration->getIndex(),
                'body' => $body
            ]);
        }
    }
}
