<?php
namespace Triadev\EsMigration;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Triadev\EsMigration\Business\Migration\CreateIndex;
use Triadev\EsMigration\Business\Migration\DeleteIndex;
use Triadev\EsMigration\Business\Migration\UpdateIndex;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
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
            if ($migration->getType()) {
                switch ($migration->getType()) {
                    case 'create':
                        (new CreateIndex())->migrate($this->esClient, $migration);
                        break;
                    case 'update':
                        (new UpdateIndex())->migrate($this->esClient, $migration);
                        break;
                    case 'delete':
                        (new DeleteIndex())->migrate($this->esClient, $migration);
                        break;
                    default:
                        break;
                }
            }
            
            (new \Triadev\EsMigration\Business\Migration\Alias())->migrate($this->esClient, $migration);
            (new \Triadev\EsMigration\Business\Migration\Reindex())->migrate($this->esClient, $migration);
        }
        
        $this->migrationRepository->createOrUpdate($version, 'done');
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
            $migration = new Migration(array_get($migrationsConfig, 'index'));
            
            $migration->setType(array_get($migrationsConfig, 'type'));
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
            
            $migration = $this->buildReindexConfig($migration, $migrationsConfig);
            
            $result[] = $migration;
        }
        
        return $result;
    }
    
    private function buildReindexConfig(Migration $migration, array $migrationsConfig) : Migration
    {
        if ($reindexConfig = array_get($migrationsConfig, 'reindex')) {
            $reindex = new Reindex(array_get($reindexConfig, 'index'));
        
            if ($refreshIndex = array_get($reindexConfig, 'refresh')) {
                $reindex->setRefresh($refreshIndex);
            }
    
            if ($versionType = array_get($reindexConfig, 'versionType')) {
                $reindex->setVersionType($versionType);
            }
        
            $migration->setReindex($reindex);
        }
        
        return $migration;
    }
}
