<?php
namespace Triadev\EsMigration;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Triadev\EsMigration\Business\Migration\Alias;
use Triadev\EsMigration\Business\Migration\CreateIndex;
use Triadev\EsMigration\Business\Migration\DeleteByQuery;
use Triadev\EsMigration\Business\Migration\DeleteIndex;
use Triadev\EsMigration\Business\Migration\Reindex;
use Triadev\EsMigration\Business\Migration\UpdateByQuery;
use Triadev\EsMigration\Business\Migration\UpdateIndex;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;

use Triadev\EsMigration\Models\Migrations\CreateIndex as CreateIndexModel;
use Triadev\EsMigration\Models\Migrations\UpdateIndex as UpdateIndexModel;
use Triadev\EsMigration\Models\Migrations\DeleteIndex as DeleteIndexModel;
use Triadev\EsMigration\Models\Migrations\Alias as AliasModel;
use Triadev\EsMigration\Models\Migrations\DeleteByQuery as DeleteByQueryModel;
use Triadev\EsMigration\Models\Migrations\UpdateByQuery as UpdateByQueryModel;
use Triadev\EsMigration\Models\Migrations\Reindex as ReindexModel;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /** @var Client */
    private $esClient;
    
    /** @var string|null */
    private $filePathMigrations;
    
    /** @var \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract */
    private $migrationRepository;
    
    /**
     * ElasticsearchMigration constructor.
     */
    public function __construct()
    {
        $this->esClient = $this->buildElasticsearchClient();
        
        $this->filePathMigrations = config('triadev-elasticsearch-migration.migration.filePath');
        
        $this->migrationRepository = app(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract::class
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
    public function migrate(string $version, string $source = 'file')
    {
        $migration = $this->migrationRepository->find($version);
        if ($migration && $migration->status == 'done') {
            throw new MigrationAlreadyDone();
        }
        
        try {
            $migrations = [];
            
            if ($source == self::MIGRATION_SOURCE_TYPE_FILE) {
                $migrations = require sprintf(
                    "%s/%s/migrations.php",
                    $this->filePathMigrations,
                    $version
                );
            } elseif ($source == self::MIGRATION_SOURCE_TYPE_DATABASE) {
                /** @var ElasticsearchMigrationDatabaseContract $elasticsearchDatabaseService */
                $elasticsearchDatabaseService = app(ElasticsearchMigrationDatabaseContract::class);
                $migrations = $elasticsearchDatabaseService->getMigration($version);
            }
            
            if (!empty($migrations)) {
                foreach ($migrations as $migration) {
                    switch (get_class($migration)) {
                        case CreateIndexModel::class:
                            (new CreateIndex())->migrate($this->esClient, $migration);
                            break;
                        case UpdateIndexModel::class:
                            (new UpdateIndex())->migrate($this->esClient, $migration);
                            break;
                        case DeleteIndexModel::class:
                            (new DeleteIndex())->migrate($this->esClient, $migration);
                            break;
                        case AliasModel::class:
                            (new Alias())->migrate($this->esClient, $migration);
                            break;
                        case DeleteByQueryModel::class:
                            (new DeleteByQuery())->migrate($this->esClient, $migration);
                            break;
                        case UpdateByQueryModel::class:
                            (new UpdateByQuery())->migrate($this->esClient, $migration);
                            break;
                        case ReindexModel::class:
                            (new Reindex())->migrate($this->esClient, $migration);
                            break;
                        default:
                            break;
                    }
                }
    
                $this->migrationRepository->createOrUpdate($version, 'done');
            }
        } catch (\Exception $e) {
            $this->migrationRepository->createOrUpdate($version, 'error');
            
            throw $e;
        }
    }
}
