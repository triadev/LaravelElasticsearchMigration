<?php
namespace Triadev\EsMigration;

use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
use Triadev\EsMigration\Business\Service\MigrationSteps;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Exception\MigrationAlreadyDone;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Exception\MigrationAlreadyRunning;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration as ElasticsearchMigrationEntity;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract as EsMigrationRepositoryInterface;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract as EsMigrationStepRepositoryInterface;
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
     * Delete migration step
     *
     * @param int $migrationStepId
     * @return bool
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
                
                $migrationStepData['id'] = (int)$migrationStepData['id'];
                $migrationStepData['status'] = (int)$migrationStepData['status'];
                $migrationStepData['params'] = json_decode($migrationStepData['params'], true);
                $migrationStepData['priority'] = (int)$migrationStepData['priority'];
                $migrationStepData['stop_on_failure'] = (bool)$migrationStepData['stop_on_failure'];
                $migrationStepData['created_at'] = new \DateTime($migrationStepData['created_at']);
                $migrationStepData['updated_at'] = new \DateTime($migrationStepData['updated_at']);
                
                $migrationSteps[] = $migrationStepData;
            }
        }
    
        return [
            'migration' => $migration,
            'status' => (int)$status,
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
    
    private function checkIfMigrationAlreadyDone(string $migration)
    {
        $migrationEntity = $this->migrationRepository->find($migration);
        
        if ($migrationEntity instanceof ElasticsearchMigrationEntity &&
            $migrationEntity->status == MigrationStatus::MIGRATION_STATUS_DONE) {
            throw new MigrationAlreadyDone();
        }
    }
    
    private function checkIfMigrationAlreadyRunning(string $migration)
    {
        $migrationEntity = $this->migrationRepository->find($migration);
        
        if ($migrationEntity instanceof ElasticsearchMigrationEntity &&
            $migrationEntity->status == MigrationStatus::MIGRATION_STATUS_RUNNING) {
            throw new MigrationAlreadyRunning();
        }
    }
    
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
        } catch (\Exception $e) {
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
}
