<?php
namespace Triadev\EsMigration\Business\Migration;

use Elasticsearch\Client;
use Triadev\EsMigration\Business\Validation\FieldDatatypeMigration;
use Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed;

class UpdateIndex
{
    /**
     * Migrate
     *
     * @param Client $esClient
     * @param \Triadev\EsMigration\Models\Migrations\UpdateIndex $migration
     *
     * @throws FieldDatatypeMigrationFailed
     */
    public function migrate(Client $esClient, \Triadev\EsMigration\Models\Migrations\UpdateIndex $migration)
    {
        if ($migration->getMappings()) {
            (new FieldDatatypeMigration())->validate(
                $esClient->indices()->getMapping(
                    ['index' => $migration->getIndex()]
                )[$migration->getIndex()]['mappings'],
                $migration->getMappings()
            );
        
            $this->updateMappings($esClient, $migration);
        }
    
        if ($migration->getSettings()) {
            $this->updateSettings($esClient, $migration);
        }
    }
    
    private function updateMappings(Client $esClient, \Triadev\EsMigration\Models\Migrations\UpdateIndex $migration)
    {
        foreach ($migration->getMappings() as $type => $mapping) {
            $esClient->indices()->putMapping([
                'index' => $migration->getIndex(),
                'type' => $type,
                'body' => $mapping
            ]);
        }
    }
    
    private function updateSettings(Client $esClient, \Triadev\EsMigration\Models\Migrations\UpdateIndex $migration)
    {
        if ($migration->isCloseIndex()) {
            $esClient->indices()->close([
                'index' => $migration->getIndex()
            ]);
        }
        
        $esClient->indices()->putSettings([
            'index' => $migration->getIndex(),
            'body' => $migration->getSettings()
        ]);
        
        if ($migration->isCloseIndex()) {
            $esClient->indices()->open([
                'index' => $migration->getIndex()
            ]);
        }
    }
}
