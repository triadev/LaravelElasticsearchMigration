<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;

class CreateIndex
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param \Triadev\EsMigration\Models\Migrations\CreateIndex $migration
     */
    public function migrate(Client $esClient, \Triadev\EsMigration\Models\Migrations\CreateIndex $migration)
    {
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
