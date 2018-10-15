<?php
namespace Triadev\EsMigration;

use Triadev\EsMigration\Business\Factory\MigrationBuilder;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepAliasContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepCreateIndexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepDeleteByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepReindexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateIndexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex;

class ElasticsearchMigrationDatabase implements ElasticsearchMigrationDatabaseContract
{
    /** @var ElasticsearchMigrationContract */
    private $elasticsearchMigrationRepository;
    
    /** @var ElasticsearchMigrationStepContract */
    private $elasticsearchMigrationStepRepository;
    
    /**
     * ElasticsearchMigrationDatabase constructor.
     * @param ElasticsearchMigrationContract $elasticsearchMigrationRepository
     * @param ElasticsearchMigrationStepContract $elasticsearchMigrationStepRepository
     */
    public function __construct(
        ElasticsearchMigrationContract $elasticsearchMigrationRepository,
        ElasticsearchMigrationStepContract $elasticsearchMigrationStepRepository
    ) {
        $this->elasticsearchMigrationRepository = $elasticsearchMigrationRepository;
        $this->elasticsearchMigrationStepRepository = $elasticsearchMigrationStepRepository;
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
        if (!$this->isMigrationTypeIsValid($type)) {
            return false;
        }
        
        try {
            if ($dbMigration = $this->elasticsearchMigrationRepository->find($migration)) {
                $migrationStep = $this->elasticsearchMigrationStepRepository->create(
                    $dbMigration->id,
                    $type,
                    $index
                );
                
                switch ($type) {
                    case self::MIGRATION_TYPE_CREATE_INDEX:
                        $this->createIndexMigration($migrationStep->id, $params);
                        break;
                    case self::MIGRATION_TYPE_UPDATE_INDEX:
                        $this->updateIndexMigration($migrationStep->id, $params);
                        break;
                    case self::MIGRATION_TYPE_DELETE_INDEX:
                        break;
                    case self::MIGRATION_TYPE_ALIAS:
                        $this->aliasMigration($migrationStep->id, $params);
                        break;
                    case self::MIGRATION_TYPE_DELETE_BY_QUERY:
                        $this->deleteByQueryMigration($migrationStep->id, $params);
                        break;
                    case self::MIGRATION_TYPE_UPDATE_BY_QUERY:
                        $this->updateByQueryMigration($migrationStep->id, $params);
                        break;
                    case self::MIGRATION_TYPE_REINDEX:
                        $this->reindexMigration($migrationStep->id, $params);
                        break;
                    default:
                        break;
                }
                
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }
        
        return false;
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
            foreach ($dbMigration->migrationSteps()->getResults() as $dbMigration) {
                /** @var ElasticsearchMigrationStep $dbMigration */
                $migrationByType = $dbMigration->migrationByType();
                if ($migrationByType) {
                    $migrationByType = is_object($migrationByType) ? $migrationByType->first() : $migrationByType;
                    
                    $index = $dbMigration->index;
                    
                    switch ($dbMigration->type) {
                        case self::MIGRATION_TYPE_CREATE_INDEX:
                            if ($migrationByType instanceof ElasticsearchMigrationStepCreateIndex) {
                                $settings = $migrationByType->settings;
                                
                                $migrations[$dbMigration->id] = MigrationBuilder::createIndex(
                                    $index,
                                    json_decode($migrationByType->mappings, true),
                                    $settings != null ? json_decode($settings, true) : null
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_UPDATE_INDEX:
                            if ($migrationByType instanceof ElasticsearchMigrationStepUpdateIndex) {
                                $mappings = $migrationByType->mappings;
                                $settings = $migrationByType->settings;
        
                                $migrations[$dbMigration->id] = MigrationBuilder::updateIndex(
                                    $index,
                                    $mappings != null ? json_decode($mappings, true) : null,
                                    $settings != null ? json_decode($settings, true) : null,
                                    (bool)$migrationByType->getAttribute('close_index')
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_DELETE_INDEX:
                            $migrations[$dbMigration->id] = MigrationBuilder::deleteIndex($index);
                            break;
                        case self::MIGRATION_TYPE_ALIAS:
                            if ($migrationByType instanceof ElasticsearchMigrationStepAlias) {
                                $add = $migrationByType->add;
                                $remove = $migrationByType->remove;
                                $removeIndices = $migrationByType->remove_indices;
                                
                                $migrations[$dbMigration->id] = MigrationBuilder::alias(
                                    $index,
                                    $add != null ? json_decode($add, true) : null,
                                    $remove != null ? json_decode($remove, true) : null,
                                    $removeIndices != null ? json_decode($removeIndices, true) : null
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_DELETE_BY_QUERY:
                            if ($migrationByType instanceof ElasticsearchMigrationStepDeleteByQuery) {
                                $migrations[$dbMigration->id] = MigrationBuilder::deleteByQuery(
                                    $index,
                                    json_decode($migrationByType->query, true),
                                    $migrationByType->type,
                                    json_decode($migrationByType->options, true)
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_UPDATE_BY_QUERY:
                            if ($migrationByType instanceof ElasticsearchMigrationStepUpdateByQuery) {
                                $script = $migrationByType->script;
                                
                                $migrations[$dbMigration->id] = MigrationBuilder::updateByQuery(
                                    $index,
                                    json_decode($migrationByType->query, true),
                                    $migrationByType->type,
                                    $script != null ? json_decode($script, true) : null,
                                    json_decode($migrationByType->options, true)
                                );
                            }
                            break;
                        case self::MIGRATION_TYPE_REINDEX:
                            if ($migrationByType instanceof ElasticsearchMigrationStepReindex) {
                                $migrations[$dbMigration->id] = MigrationBuilder::reindex(
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
     * Get migration steps
     *
     * @param string $migration
     * @return array
     */
    public function getMigrationSteps(string $migration) : array
    {
        $migrationSteps = [];
        
        if ($dbMigration = $this->elasticsearchMigrationRepository->find($migration)) {
            $dbMigrationSteps = $dbMigration->migrationSteps();
            
            foreach ($dbMigrationSteps->cursor() as $dbMigrationStep) {
                /** @var ElasticsearchMigrationStep $dbMigrationStep */
                $migrationSteps[] = array_except($dbMigrationStep->toArray(), [
                    'id',
                    'migration_id'
                ]);
            }
        }
        
        return $migrationSteps;
    }
    
    private function isMigrationTypeIsValid(string $type) : bool
    {
        if (in_array($type, [
            self::MIGRATION_TYPE_CREATE_INDEX,
            self::MIGRATION_TYPE_UPDATE_INDEX,
            self::MIGRATION_TYPE_DELETE_INDEX,
            self::MIGRATION_TYPE_ALIAS,
            self::MIGRATION_TYPE_DELETE_BY_QUERY,
            self::MIGRATION_TYPE_UPDATE_BY_QUERY,
            self::MIGRATION_TYPE_REINDEX,
        ])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param int $migrationStepId
     * @param array $params
     * @throws \Throwable
     */
    private function createIndexMigration(int $migrationStepId, array $params)
    {
        /** @var ElasticsearchMigrationStepCreateIndexContract $repository */
        $repository = app(ElasticsearchMigrationStepCreateIndexContract::class);
        
        $repository->create(
            $migrationStepId,
            array_get($params, 'mappings'),
            array_get($params, 'settings')
        );
    }
    
    /**
     * @param int $migrationStepId
     * @param array $params
     * @throws \Throwable
     */
    private function updateIndexMigration(int $migrationStepId, array $params)
    {
        /** @var ElasticsearchMigrationStepUpdateIndexContract $repository */
        $repository = app(ElasticsearchMigrationStepUpdateIndexContract::class);
        
        $closeIndex = array_get($params, 'closeIndex');
        if (!$closeIndex) {
            $closeIndex = false;
        }
        
        $repository->create(
            $migrationStepId,
            array_get($params, 'mappings'),
            array_get($params, 'settings'),
            $closeIndex
        );
    }
    
    /**
     * @param int $migrationStepId
     * @param array $params
     * @throws \Throwable
     */
    private function aliasMigration(int $migrationStepId, array $params)
    {
        /** @var ElasticsearchMigrationStepAliasContract $repository */
        $repository = app(ElasticsearchMigrationStepAliasContract::class);
        
        $repository->create(
            $migrationStepId,
            array_has($params, 'add') ? array_get($params, 'add') : [],
            array_has($params, 'remove') ? array_get($params, 'remove') : [],
            array_has($params, 'removeIndices') ? array_get($params, 'removeIndices') : []
        );
    }
    
    /**
     * @param int $migrationStepId
     * @param array $params
     * @throws \Throwable
     */
    private function deleteByQueryMigration(int $migrationStepId, array $params)
    {
        /** @var ElasticsearchMigrationStepDeleteByQueryContract $repository */
        $repository = app(ElasticsearchMigrationStepDeleteByQueryContract::class);
        
        $repository->create(
            $migrationStepId,
            array_get($params, 'query'),
            array_get($params, 'type'),
            array_has($params, 'options') ? array_get($params, 'options') : []
        );
    }
    
    /**
     * @param int $migrationStepId
     * @param array $params
     * @throws \Throwable
     */
    private function updateByQueryMigration(int $migrationStepId, array $params)
    {
        /** @var ElasticsearchMigrationStepUpdateByQueryContract $repository */
        $repository = app(ElasticsearchMigrationStepUpdateByQueryContract::class);
        
        $repository->create(
            $migrationStepId,
            array_get($params, 'query'),
            array_get($params, 'type'),
            array_get($params, 'script'),
            array_has($params, 'options') ? array_get($params, 'options') : []
        );
    }
    
    /**
     * @param int $migrationStepId
     * @param array $params
     * @throws \Throwable
     */
    private function reindexMigration(int $migrationStepId, array $params)
    {
        /** @var ElasticsearchMigrationStepReindexContract $repository */
        $repository = app(ElasticsearchMigrationStepReindexContract::class);
    
        $refreshSourceIndex = array_get($params, 'refreshSourceIndex');
        if (!$refreshSourceIndex) {
            $refreshSourceIndex = false;
        }
        
        $repository->create(
            $migrationStepId,
            array_get($params, 'destIndex'),
            $refreshSourceIndex,
            array_has($params, 'global') ? array_get($params, 'global') : [],
            array_has($params, 'source') ? array_get($params, 'source') : [],
            array_has($params, 'dest') ? array_get($params, 'dest') : []
        );
    }
}
