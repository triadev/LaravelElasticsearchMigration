<?php
namespace Triadev\EsMigration;

use Illuminate\Support\Carbon;
use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
use Triadev\EsMigration\Business\Service\MigrationSteps;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Exception\MigrationAlreadyRunning;
use Triadev\EsMigration\Exception\MigrationStepNotFound;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration as ElasticsearchMigrationEntity;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract as EsMigrationRepositoryInterface;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract as EsMigrationStepRepositoryInterface;
use Triadev\EsMigration\Models\Migration;
use Triadev\EsMigration\Models\MigrationStep;

class ElasticsearchMigration implements ElasticsearchMigrationContract
{
    /** @var EsMigrationRepositoryInterface */
    private $migrationRepository;
    
    /** @var EsMigrationStepRepositoryInterface */
    private $migrationStepRepository;
    
    /** @var MigrationSteps */
    private $migrationStepService;
    
    /**
     * ElasticsearchMigration constructor.
     */
    public function __construct()
    {
        $this->migrationRepository = app(EsMigrationRepositoryInterface::class);
        $this->migrationStepRepository = app(EsMigrationStepRepositoryInterface::class);
        $this->migrationStepService = app(MigrationSteps::class);
    }
    
    /**
     * @inheritdoc
     */
    public function createMigration(string $migration): bool
    {
        try {
            $this->migrationRepository->createOrUpdate($migration);
        } catch (\Throwable $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function deleteMigration(string $migration) : bool
    {
        try {
            $this->migrationRepository->delete($migration);
        } catch (\Throwable $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function addMigrationStep(
        string $migration,
        string $type,
        array $params = [],
        int $priority = 1,
        bool $stopOnFailure = true
    ) : bool {
        if (!(new MigrationTypes())->isMigrationTypeValid($type)) {
            return false;
        }
        
        try {
            if ($migration = $this->migrationRepository->find($migration)) {
                $this->migrationStepRepository->create(
                    $migration->id,
                    $type,
                    $params,
                    $priority,
                    $stopOnFailure
                );
                
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }
        
        return false;
    }
    
    /**
     * @inheritdoc
     */
    public function deleteMigrationStep(int $migrationStepId) : bool
    {
        try {
            $this->migrationStepRepository->delete($migrationStepId);
        } catch (\Throwable $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function startSingleMigrationStep(int $migrationStepId, ElasticsearchClients $elasticsearchClients)
    {
        if ($migrationStep = $this->migrationStepService->getMigrationStep($migrationStepId)) {
            $this->startMigrationStep($migrationStep, $elasticsearchClients);
        } else {
            throw new MigrationStepNotFound();
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getMigrationStatus(string $migration) : array
    {
        $migrationSteps = [];
        
        $status = null;
        $error = null;
    
        if ($migrationEntity = $this->migrationRepository->find($migration)) {
            $status = $migrationEntity->status;
            $error = $migrationEntity->error;
            
            foreach ($migrationEntity->migrationSteps()->cursor() as $migrationStep) {
                /** @var ElasticsearchMigrationStep $migrationStep */
                $migrationStepData = array_except($migrationStep->toArray(), ['migration_id']);
                
                $migrationStepData['id'] = (int) $migrationStepData['id'];
                $migrationStepData['status'] = (int) $migrationStepData['status'];
                $migrationStepData['params'] = json_decode($migrationStepData['params'], true);
                $migrationStepData['priority'] = (int) $migrationStepData['priority'];
                $migrationStepData['stop_on_failure'] = (bool) $migrationStepData['stop_on_failure'];
                $migrationStepData['created_at'] = new \DateTime($migrationStepData['created_at']);
                $migrationStepData['updated_at'] = new \DateTime($migrationStepData['updated_at']);
                
                $migrationSteps[] = $migrationStepData;
            }
        }
    
        return [
            'migration' => $migration,
            'status' => (int) $status,
            'error' => $error,
            'steps' => $migrationSteps
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function startMigration(string $migration, ElasticsearchClients $elasticsearchClients)
    {
        $this->checkIfMigrationAlreadyDone($migration);
        $this->checkIfMigrationAlreadyRunning($migration);
    
        $this->migrate($migration, $elasticsearchClients);
    }
    
    /**
     * @inheritdoc
     */
    public function restartMigration(string $migration, ElasticsearchClients $elasticsearchClients)
    {
        $this->checkIfMigrationAlreadyRunning($migration);
        
        $this->migrate($migration, $elasticsearchClients);
    }
    
    /**
     * @param string $migration
     * @param ElasticsearchClients $elasticsearchClients
     * @throws \Throwable
     */
    private function migrate(string $migration, ElasticsearchClients $elasticsearchClients)
    {
        try {
            $migrationSteps = $this->migrationStepService->getMigrationSteps($migration, true);
        
            if (!empty($migrationSteps)) {
                $this->migrationRepository->createOrUpdate($migration, MigrationStatus::MIGRATION_STATUS_RUNNING);
            
                foreach ($migrationSteps as $migrationStep) {
                    $this->startMigrationStep($migrationStep, $elasticsearchClients);
                }
            
                $this->migrationRepository->createOrUpdate($migration, MigrationStatus::MIGRATION_STATUS_DONE);
            }
        } catch (\Exception $e) {
            $this->migrationRepository->createOrUpdate(
                $migration,
                MigrationStatus::MIGRATION_STATUS_ERROR,
                $e->getMessage()
            );
        }
    }
    
    /**
     * @param string $migration
     * @throws MigrationAlreadyDone
     */
    private function checkIfMigrationAlreadyDone(string $migration)
    {
        $migrationEntity = $this->migrationRepository->find($migration);
        
        if ($migrationEntity instanceof ElasticsearchMigrationEntity &&
            $migrationEntity->status == MigrationStatus::MIGRATION_STATUS_DONE) {
            throw new MigrationAlreadyDone();
        }
    }
    
    /**
     * @param string $migration
     * @throws MigrationAlreadyRunning
     */
    private function checkIfMigrationAlreadyRunning(string $migration)
    {
        $migrationEntity = $this->migrationRepository->find($migration);
        
        if ($migrationEntity instanceof ElasticsearchMigrationEntity &&
            $migrationEntity->status == MigrationStatus::MIGRATION_STATUS_RUNNING) {
            throw new MigrationAlreadyRunning();
        }
    }
    
    /**
     * @param MigrationStep $migrationStep
     * @param ElasticsearchClients $elasticsearchClients
     * @throws Exception\MigrationsNotExist
     * @throws \Throwable
     */
    private function startMigrationStep(
        MigrationStep $migrationStep,
        ElasticsearchClients $elasticsearchClients
    ) {
        try {
            $this->migrationStepRepository->update($migrationStep->getId(), MigrationStatus::MIGRATION_STATUS_RUNNING);
            
            foreach ($elasticsearchClients->all() as $elasticsearchClient) {
                if ($migrationClass = (new MigrationTypes())->mapTypeToClass($migrationStep->getType())) {
                    $migrationClass->migrate($elasticsearchClient, $migrationStep);
                }
            }
    
            $this->migrationStepRepository->update($migrationStep->getId(), MigrationStatus::MIGRATION_STATUS_DONE);
        } catch (\Throwable $e) {
            $this->migrationStepRepository->update(
                $migrationStep->getId(),
                MigrationStatus::MIGRATION_STATUS_ERROR,
                $e->getMessage()
            );
            
            if ($migrationStep->isStopOnFailure()) {
                throw $e;
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    public function getAllMigrations(?array $onlyWithStatus = null): array
    {
        $migrations = [];
        
        $this->migrationRepository->all()->each(function($migrationEntity) use (&$migrations, $onlyWithStatus) {
            /** @var \Triadev\EsMigration\Models\Entity\ElasticsearchMigration $migrationEntity */
            if (is_array($onlyWithStatus) && !in_array($migrationEntity->status, $onlyWithStatus)) {
                return;
            }
            
            $migrations[] = new Migration(
                $migrationEntity->id,
                $migrationEntity->migration,
                $migrationEntity->status,
                $migrationEntity->error,
                Carbon::parse($migrationEntity->created_at),
                Carbon::parse($migrationEntity->updated_at)
            );
        });
        
        return $migrations;
    }
}
