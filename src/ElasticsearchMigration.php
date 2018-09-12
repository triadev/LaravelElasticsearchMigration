<?php
namespace Triadev\EsMigration;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Exception\IndexNotExist;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;
use Triadev\EsMigration\Models\Alias;
use Triadev\EsMigration\Models\Migration;
use Triadev\EsMigration\Models\Reindex;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /** @var Client */
    private $esClient;
    
    /** @var string|null */
    private $filePathMigrations;
    
    /** @var \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract */
    private $migrationRepository;
    
    /**
     * ElasticsearchMigration constructor.
     */
    public function __construct()
    {
        $this->esClient = $this->buildElasticsearchClient();
        
        $this->filePathMigrations = config('triadev-elasticsearch-migration.migration.filePath');
        
        $this->migrationRepository = app(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::class
        );
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
        if ($this->migrationRepository->find($version)) {
            throw new MigrationAlreadyDone();
        }
        
        $migrations = $this->buildMigrations($version);
        
        foreach ($migrations as $migration) {
            switch ($migration->getType()) {
                case 'create':
                    $this->create($migration);
                    break;
                case 'update':
                    $this->update($migration);
                    break;
                case 'delete':
                    $this->delete($migration);
                    break;
                default:
                    break;
            }
    
            if ($migration->getAlias()) {
                $this->updateAlias($migration);
            }
    
            if ($migration->getReindex()) {
                if (!$this->esClient->indices()->exists(['index' => $migration->getReindex()->getIndex()])) {
                    throw new IndexNotExist();
                }
                
                if ($migration->getReindex()->isRefresh()) {
                    $this->esClient->indices()->refresh([
                        'index' => sprintf(
                            "%s,%s",
                            $migration->getIndex(),
                            $migration->getReindex()->getIndex()
                        )
                    ]);
                }
                
                $this->esClient->reindex([
                    'body' => [
                        'source' => [
                            'index' => $migration->getIndex()
                        ],
                        'dest' => [
                            'index' => $migration->getReindex()->getIndex()
                        ]
                    ]
                ]);
            }
        }
        
        $this->migrationRepository->createOrUpdate($version, 'done');
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
            $this->updateMappings($migration);
        }
        
        if ($migration->getSettings()) {
            $this->updateSettings($migration);
        }
    }
    
    private function delete(Migration $migration)
    {
        $this->esClient->indices()->delete([
            'index' => $migration->getIndex()
        ]);
    }
    
    private function updateMappings(Migration $migration)
    {
        foreach ($migration->getMappings() as $type => $mapping) {
            $this->esClient->indices()->putMapping([
                'index' => $migration->getIndex(),
                'type' => $type,
                'body' => $mapping
            ]);
        }
    }
    
    private function updateSettings(Migration $migration)
    {
        if ($migration->isCloseIndex()) {
            $this->esClient->indices()->close([
                'index' => $migration->getIndex()
            ]);
        }
    
        $this->esClient->indices()->putSettings([
            'index' => $migration->getIndex(),
            'body' => $migration->getSettings()
        ]);
    
        if ($migration->isCloseIndex()) {
            $this->esClient->indices()->open([
                'index' => $migration->getIndex()
            ]);
        }
    }
    
    private function updateAlias(Migration $migration)
    {
        if (!empty($migration->getAlias()->getAdd())) {
            foreach ($migration->getAlias()->getAdd() as $alias) {
                $this->esClient->indices()->putAlias([
                    'index' => $migration->getIndex(),
                    'name' => $alias
                ]);
            }
        }
    
        if (!empty($migration->getAlias()->getRemove())) {
            foreach ($migration->getAlias()->getRemove() as $alias) {
                $this->esClient->indices()->deleteAlias([
                    'index' => $migration->getIndex(),
                    'name' => $alias
                ]);
            }
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
            
            if ($closeIndex = array_get($migrationsConfig, 'closeIndex')) {
                $migration->setCloseIndex($closeIndex);
            }
            
            if ($aliasConfig = array_get($migrationsConfig, 'alias')) {
                $alias = new Alias();
                
                if ($add = array_get($aliasConfig, 'add')) {
                    $alias->setAdd($add);
                }
    
                if ($remove = array_get($aliasConfig, 'remove')) {
                    $alias->setRemove($remove);
                }
                
                $migration->setAlias($alias);
            }
            
            if ($reindexConfig = array_get($migrationsConfig, 'reindex')) {
                $reindex = new Reindex(array_get($reindexConfig, 'index'));
                
                if ($refreshIndex = array_get($reindexConfig, 'refresh')) {
                    $reindex->setRefresh($refreshIndex);
                }
                
                $migration->setReindex($reindex);
            }
            
            $result[] = $migration;
        }
        
        return $result;
    }
}
