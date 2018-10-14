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
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration as ElasticsearchMigrationEntity;

use Triadev\EsMigration\Models\Migrations\CreateIndex as CreateIndexModel;
use Triadev\EsMigration\Models\Migrations\UpdateIndex as UpdateIndexModel;
use Triadev\EsMigration\Models\Migrations\DeleteIndex as DeleteIndexModel;
use Triadev\EsMigration\Models\Migrations\Alias as AliasModel;
use Triadev\EsMigration\Models\Migrations\DeleteByQuery as DeleteByQueryModel;
use Triadev\EsMigration\Models\Migrations\UpdateByQuery as UpdateByQueryModel;
use Triadev\EsMigration\Models\Migrations\Reindex as ReindexModel;

use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract as EsMigrationRepositoryInterface;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract as EsMigrationStepRepositoryInterface;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /** @var Client */
    private $esClient;
    
    /** @var string|null */
    private $filePathMigrations;
    
    /** @var EsMigrationRepositoryInterface */
    private $migrationRepository;
    
    /** @var EsMigrationStepRepositoryInterface */
    private $migrationStepRepository;
    
    /**
     * ElasticsearchMigration constructor.
     */
    public function __construct()
    {
        $this->esClient = $this->buildElasticsearchClient();
        
        $this->filePathMigrations = config('triadev-elasticsearch-migration.migration.filePath');
        
        $this->migrationRepository = app(EsMigrationRepositoryInterface::class);
        $this->migrationStepRepository = app(EsMigrationStepRepositoryInterface::class);
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
        $this->checkIfMigrationAlreadyRunning($version);
        
        try {
            $updateMigrationStepStatus = $source == self::MIGRATION_SOURCE_TYPE_DATABASE ? true : false;
            
            if (!empty($migrations = $this->getMigrations($version, $source))) {
                foreach ($migrations as $migrationStepId => $migration) {
                    $this->startMigrationStep(
                        $migration,
                        $updateMigrationStepStatus ? $migrationStepId : null
                    );
                }
    
                $this->migrationRepository->createOrUpdate(
                    $version,
                    EsMigrationRepositoryInterface::ELASTICSEARCH_MIGRATION_STATUS_DONE
                );
            }
        } catch (\Exception $e) {
            $this->migrationRepository->createOrUpdate(
                $version,
                EsMigrationRepositoryInterface::ELASTICSEARCH_MIGRATION_STATUS_ERROR
            );
            
            throw $e;
        }
    }
    
    /**
     * @param string $version
     * @throws MigrationAlreadyDone
     */
    private function checkIfMigrationAlreadyRunning(string $version)
    {
        $migration = $this->migrationRepository->find($version);
        
        if ($migration instanceof ElasticsearchMigrationEntity &&
            $migration->status == EsMigrationRepositoryInterface::ELASTICSEARCH_MIGRATION_STATUS_DONE) {
            throw new MigrationAlreadyDone();
        }
    }
    
    /**
     * @param string $version
     * @param string $source
     * @return array
     */
    private function getMigrations(string $version, string $source) : array
    {
        if ($source == self::MIGRATION_SOURCE_TYPE_FILE) {
            return  require sprintf("%s/%s/migrations.php", $this->filePathMigrations, $version);
        } elseif ($source == self::MIGRATION_SOURCE_TYPE_DATABASE) {
            /** @var ElasticsearchMigrationDatabaseContract $elasticsearchDatabaseService */
            $elasticsearchDatabaseService = app(ElasticsearchMigrationDatabaseContract::class);
            return $elasticsearchDatabaseService->getMigration($version);
        }
        
        return [];
    }
    
    /**
     * @param $migration
     * @param int|null $migrationStepId
     * @throws Exception\MigrationsNotExist
     * @throws \Throwable
     */
    private function startMigrationStep($migration, ?int $migrationStepId = null)
    {
        try {
            if ($migrationStepId) {
                $this->migrationStepRepository->update(
                    $migrationStepId,
                    ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_RUNNING
                );
            }
            
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
        } catch (\Exception $e) {
            if ($migrationStepId) {
                $this->migrationStepRepository->update(
                    $migrationStepId,
                    ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_ERROR
                );
            } else {
                throw $e;
            }
        }
        
        if ($migrationStepId) {
            $this->migrationStepRepository->update(
                $migrationStepId,
                ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE
            );
        }
    }
}
