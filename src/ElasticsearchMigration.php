<?php
namespace Triadev\EsMigration;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Models\Migration;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /** @var Client */
    private $esClient;
    
    /** @var string|null */
    private $filePathMigrations;
    
    /**
     * ElasticsearchMigration constructor.
     */
    public function __construct()
    {
        $this->esClient = $this->buildElasticsearchClient();
        
        $this->filePathMigrations = config('triadev-elasticsearch-migration.migration.filePath');
    }
    
    private function buildElasticsearchClient() : Client
    {
        $config = config('triadev-elasticsearch-migration');
        
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts([
            [
                'host' => $config['host'],
                'port' => $config['port'],
                'scheme' => $config['scheme'],
                'user' => $config['user'],
                'pass' => $config['pass']
            ]
        ]);
        
        return $clientBuilder->build();
    }
    
    /**
     * @inheritdoc
     */
    public function migrate(string $version)
    {
        $migrations = $this->buildMigrations($version);
        
        foreach ($migrations as $migration) {
            switch ($migration->getType()) {
                case 'create':
                    $this->create($migration);
                    break;
                case 'update':
                    $this->update($migration);
                    break;
                default:
                    break;
            }
        }
    }
    
    private function create(Migration $migration)
    {
        $body = [
            'mappings' => $migration->getMappings()
        ];
        
        if ($migration->getSettings()) {
            $body['settings'] = $migration->getSettings();
        }
        
        $this->esClient->indices()->create([
            'index' => $migration->getIndex(),
            'body' => $body
        ]);
    }
    
    private function update(Migration $migration)
    {
        if ($migration->getMappings()) {
            foreach ($migration->getMappings() as $type => $mapping) {
                $this->esClient->indices()->putMapping([
                    'index' => $migration->getIndex(),
                    'type' => $type,
                    'body' => $mapping
                ]);
            }
        }
        
        if ($migration->getSettings()) {
            $this->esClient->indices()->putSettings([
                'index' => $migration->getIndex(),
                'body' => [
                    'index' => $migration->getSettings()
                ]
            ]);
        }
    }
    
    /**
     * @param string $version
     * @return Migration[]
     */
    private function buildMigrations(string $version) : array
    {
        $migrationsConfigs = require sprintf("%s/%s/migrations.php", $this->filePathMigrations, $version);
        
        $result = [];
        
        foreach ($migrationsConfigs as $migrationsConfig) {
            $migration = new Migration(
                array_get($migrationsConfig, 'index'),
                array_get($migrationsConfig, 'type')
            );
            
            $migration->setMappings(array_get($migrationsConfig, 'mappings'));
            $migration->setSettings(array_get($migrationsConfig, 'settings'));
            
            $result[] = $migration;
        }
        
        return $result;
    }
}
