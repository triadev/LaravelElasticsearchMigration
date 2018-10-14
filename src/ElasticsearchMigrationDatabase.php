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
    
    /**
     * ElasticsearchMigrationDatabase constructor.
     * @param ElasticsearchMigrationContract $elasticsearchMigrationRepository
     * @param ElasticsearchMigrationsContract $elasticsearchMigrationsRepository
     */
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
                $dbMigration->id,
                $type,
                $index
            );
            
            $migrationsId = $migrations->id;
    
            switch ($type) {
                case self::MIGRATION_TYPE_CREATE_INDEX:
                    $this->createIndexMigration($migrationsId, $params);
                    break;
                case self::MIGRATION_TYPE_UPDATE_INDEX:
                    $this->updateIndexMigration($migrationsId, $params);
                    break;
                case self::MIGRATION_TYPE_DELETE_INDEX:
                    break;
                case self::MIGRATION_TYPE_ALIAS:
                    $this->aliasMigration($migrationsId, $params);
                    break;
                case self::MIGRATION_TYPE_DELETE_BY_QUERY:
                    $this->deleteByQueryMigration($migrationsId, $params);
                    break;
                case self::MIGRATION_TYPE_UPDATE_BY_QUERY:
                    $this->updateByQueryMigration($migrationsId, $params);
                    break;
                case self::MIGRATION_TYPE_REINDEX:
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
                    
                    $index = $dbMigration->index;
                    
                    switch ($dbMigration->type) {
                        case self::MIGRATION_TYPE_CREATE_INDEX:
                            if ($migrationByType instanceof ElasticsearchMigrationsCreateIndex) {
                                $settings = $migrationByType->settings;
                                
                                $migrations[] = MigrationBuilder::createIndex(
                                    $index,
                                    json_decode($migrationByType->mappings, true),
                                    $settings != null ? json_decode($settings, true) : null
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_UPDATE_INDEX:
                            if ($migrationByType instanceof ElasticsearchMigrationsUpdateIndex) {
                                $mappings = $migrationByType->mappings;
                                $settings = $migrationByType->settings;
        
                                $migrations[] = MigrationBuilder::updateIndex(
                                    $index,
                                    $mappings != null ? json_decode($mappings, true) : null,
                                    $settings != null ? json_decode($settings, true) : null,
                                    (bool)$migrationByType->getAttribute('close_index')
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_DELETE_INDEX:
                            $migrations[] = MigrationBuilder::deleteIndex($index);
                            break;
                        case self::MIGRATION_TYPE_ALIAS:
                            if ($migrationByType instanceof ElasticsearchMigrationsAlias) {
                                $add = $migrationByType->add;
                                $remove = $migrationByType->remove;
                                $removeIndices = $migrationByType->remove_indices;
                                
                                $migrations[] = MigrationBuilder::alias(
                                    $index,
                                    $add != null ? json_decode($add, true) : null,
                                    $remove != null ? json_decode($remove, true) : null,
                                    $removeIndices != null ? json_decode($removeIndices, true) : null
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_DELETE_BY_QUERY:
                            if ($migrationByType instanceof ElasticsearchMigrationsDeleteByQuery) {
                                $migrations[] = MigrationBuilder::deleteByQuery(
                                    $index,
                                    json_decode($migrationByType->query, true),
                                    $migrationByType->type,
                                    json_decode($migrationByType->options, true)
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_UPDATE_BY_QUERY:
                            if ($migrationByType instanceof ElasticsearchMigrationsUpdateByQuery) {
                                $script = $migrationByType->script;
                                
                                $migrations[] = MigrationBuilder::updateByQuery(
                                    $index,
                                    json_decode($migrationByType->query, true),
                                    $migrationByType->type,
                                    $script != null ? json_decode($script, true) : null,
                                    json_decode($migrationByType->options, true)
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_REINDEX:
                            if ($migrationByType instanceof ElasticsearchMigrationsReindex) {
                                $migrations[] = MigrationBuilder::reindex(
                                    $index,
                                    $migrationByType->dest_index,
                                    (bool)$migrationByType->refresh_source_index,
                                    json_decode($migrationByType->global, true),
                                    json_decode($migrationByType->source, true),
                                    json_decode($migrationByType->dest, true)
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
    
    /**
     * @param int $migrationsId
     * @param array $params
     * @throws \Throwable
     */
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
    
    /**
     * @param int $migrationsId
     * @param array $params
     * @throws \Throwable
     */
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
    
    /**
     * @param int $migrationsId
     * @param array $params
     * @throws \Throwable
     */
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
    
    /**
     * @param int $migrationsId
     * @param array $params
     * @throws \Throwable
     */
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
    
    /**
     * @param int $migrationsId
     * @param array $params
     * @throws \Throwable
     */
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
    
    /**
     * @param int $migrationsId
     * @param array $params
     * @throws \Throwable
     */
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
