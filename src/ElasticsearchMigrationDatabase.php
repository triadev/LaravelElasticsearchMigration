<?php
namespace Triadev\EsMigration;

use Triadev\EsMigration\Business\Factory\MigrationBuilder;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsAliasContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsCreateIndexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsDeleteByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsReindexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateIndexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrations;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex;

class ElasticsearchMigrationDatabase implements ElasticsearchMigrationDatabaseContract
{
    /** @var ElasticsearchMigrationContract */
    private $elasticsearchMigrationRepository;
    
    /** @var ElasticsearchMigrationsContract */
    private $elasticsearchMigrationsRepository;
    
    public function __construct(
        ElasticsearchMigrationContract $elasticsearchMigrationRepository,
        ElasticsearchMigrationsContract $elasticsearchMigrationsRepository
    ) {
        $this->elasticsearchMigrationRepository = $elasticsearchMigrationRepository;
        $this->elasticsearchMigrationsRepository = $elasticsearchMigrationsRepository;
    }
    
    /**
     * @inheritdoc
     */
    public function createMigration(string $migration): bool
    {
        try {
            $this->elasticsearchMigrationRepository->createOrUpdate($migration);
        } catch (\Throwable $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function addMigration(string $migration, string $type, string $index, array $params = []) : bool
    {
        $dbMigration = $this->elasticsearchMigrationRepository->find($migration);
        if (!$dbMigration) {
            return false;
        }
    
        try {
            $migrations = $this->elasticsearchMigrationsRepository->create(
                $dbMigration->getAttribute('id'),
                $type,
                $index
            );
            
            $migrationsId = $migrations->getAttribute('id');
    
            switch ($type) {
                case 'createIndex':
                    $this->createIndexMigration($migrationsId, $params);
                    break;
                case 'updateIndex':
                    $this->updateIndexMigration($migrationsId, $params);
                    break;
                case 'deleteIndex':
                    break;
                case 'alias':
                    $this->aliasMigration($migrationsId, $params);
                    break;
                case 'deleteByQuery':
                    $this->deleteByQueryMigration($migrationsId, $params);
                    break;
                case 'updateByQuery':
                    $this->updateByQueryMigration($migrationsId, $params);
                    break;
                case 'reindex':
                    $this->reindexMigration($migrationsId, $params);
                    break;
                default:
                    break;
            }
        } catch (\Throwable $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get migration
     *
     * @param string $migration
     * @return array
     */
    public function getMigration(string $migration) : array
    {
        $migrations = [];
        
        if ($dbMigration = $this->elasticsearchMigrationRepository->find($migration)) {
            foreach ($dbMigration->migrations()->getResults() as $dbMigration) {
                /** @var ElasticsearchMigrations $dbMigration */
                $migrationByType = $dbMigration->migrationByType();
                if ($migrationByType) {
                    $migrationByType = is_object($migrationByType) ? $migrationByType->first() : $migrationByType;
                    
                    $index = $dbMigration->getAttribute('index');
                    
                    switch ($dbMigration->getAttribute('type')) {
                        case 'createIndex':
                            if ($migrationByType instanceof ElasticsearchMigrationsCreateIndex) {
                                $settings = $migrationByType->getAttribute('settings');
                                
                                $migrations[] = MigrationBuilder::createIndex(
                                    $index,
                                    json_decode($migrationByType->getAttribute('mappings'), true),
                                    $settings != null ? json_decode($settings, true) : null
                                );
                            }
                            break;
                        case 'updateIndex':
                            if ($migrationByType instanceof ElasticsearchMigrationsUpdateIndex) {
                                $mappings = $migrationByType->getAttribute('mappings');
                                $settings = $migrationByType->getAttribute('settings');
        
                                $migrations[] = MigrationBuilder::updateIndex(
                                    $index,
                                    $mappings != null ? json_decode($mappings, true) : null,
                                    $settings != null ? json_decode($settings, true) : null,
                                    (bool)$migrationByType->getAttribute('close_index')
                                );
                            }
                            break;
                        case 'deleteIndex':
                            $migrations[] = MigrationBuilder::deleteIndex($index);
                            break;
                        case 'alias':
                            if ($migrationByType instanceof ElasticsearchMigrationsAlias) {
                                $add = $migrationByType->getAttribute('add');
                                $remove = $migrationByType->getAttribute('remove');
                                $removeIndices = $migrationByType->getAttribute('remove_indices');
                                
                                $migrations[] = MigrationBuilder::alias(
                                    $index,
                                    $add != null ? json_decode($add, true) : null,
                                    $remove != null ? json_decode($remove, true) : null,
                                    $removeIndices != null ? json_decode($removeIndices, true) : null
                                );
                            }
                            break;
                        case 'deleteByQuery':
                            if ($migrationByType instanceof ElasticsearchMigrationsDeleteByQuery) {
                                $migrations[] = MigrationBuilder::deleteByQuery(
                                    $index,
                                    json_decode($migrationByType->getAttribute('query'), true),
                                    $migrationByType->getAttribute('type'),
                                    json_decode($migrationByType->getAttribute('options'), true)
                                );
                            }
                            break;
                        case 'updateByQuery':
                            if ($migrationByType instanceof ElasticsearchMigrationsUpdateByQuery) {
                                $script = $migrationByType->getAttribute('script');
                                
                                $migrations[] = MigrationBuilder::updateByQuery(
                                    $index,
                                    json_decode($migrationByType->getAttribute('query'), true),
                                    $migrationByType->getAttribute('type'),
                                    $script != null ? json_decode($script, true) : null,
                                    json_decode($migrationByType->getAttribute('options'), true)
                                );
                            }
                            break;
                        case 'reindex':
                            if ($migrationByType instanceof ElasticsearchMigrationsReindex) {
                                $migrations[] = MigrationBuilder::reindex(
                                    $index,
                                    $migrationByType->getAttribute('dest_index'),
                                    (bool)$migrationByType->getAttribute('refresh_source_index'),
                                    json_decode($migrationByType->getAttribute('global'), true),
                                    json_decode($migrationByType->getAttribute('source'), true),
                                    json_decode($migrationByType->getAttribute('dest'), true)
                                );
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        
        return $migrations;
    }
    
    private function createIndexMigration(int $migrationsId, array $params)
    {
        /** @var ElasticsearchMigrationsCreateIndexContract $repository */
        $repository = app(ElasticsearchMigrationsCreateIndexContract::class);
        
        $repository->create(
            $migrationsId,
            array_get($params, 'mappings'),
            array_get($params, 'settings')
        );
    }
    
    private function updateIndexMigration(int $migrationsId, array $params)
    {
        /** @var ElasticsearchMigrationsUpdateIndexContract $repository */
        $repository = app(ElasticsearchMigrationsUpdateIndexContract::class);
        
        $closeIndex = array_get($params, 'closeIndex');
        if (!$closeIndex) {
            $closeIndex = false;
        }
        
        $repository->create(
            $migrationsId,
            array_get($params, 'mappings'),
            array_get($params, 'settings'),
            $closeIndex
        );
    }
    
    private function aliasMigration(int $migrationsId, array $params)
    {
        /** @var ElasticsearchMigrationsAliasContract $repository */
        $repository = app(ElasticsearchMigrationsAliasContract::class);
        
        $repository->create(
            $migrationsId,
            array_has($params, 'add') ? array_get($params, 'add') : [],
            array_has($params, 'remove') ? array_get($params, 'remove') : [],
            array_has($params, 'removeIndices') ? array_get($params, 'removeIndices') : []
        );
    }
    
    private function deleteByQueryMigration(int $migrationsId, array $params)
    {
        /** @var ElasticsearchMigrationsDeleteByQueryContract $repository */
        $repository = app(ElasticsearchMigrationsDeleteByQueryContract::class);
        
        $repository->create(
            $migrationsId,
            array_get($params, 'query'),
            array_get($params, 'type'),
            array_has($params, 'options') ? array_get($params, 'options') : []
        );
    }
    
    private function updateByQueryMigration(int $migrationsId, array $params)
    {
        /** @var ElasticsearchMigrationsUpdateByQueryContract $repository */
        $repository = app(ElasticsearchMigrationsUpdateByQueryContract::class);
        
        $repository->create(
            $migrationsId,
            array_get($params, 'query'),
            array_get($params, 'type'),
            array_get($params, 'script'),
            array_has($params, 'options') ? array_get($params, 'options') : []
        );
    }
    
    private function reindexMigration(int $migrationsId, array $params)
    {
        /** @var ElasticsearchMigrationsReindexContract $repository */
        $repository = app(ElasticsearchMigrationsReindexContract::class);
    
        $refreshSourceIndex = array_get($params, 'refreshSourceIndex');
        if (!$refreshSourceIndex) {
            $refreshSourceIndex = false;
        }
        
        $repository->create(
            $migrationsId,
            array_get($params, 'destIndex'),
            $refreshSourceIndex,
            array_has($params, 'global') ? array_get($params, 'global') : [],
            array_has($params, 'source') ? array_get($params, 'source') : [],
            array_has($params, 'dest') ? array_get($params, 'dest') : []
        );
    }
}
