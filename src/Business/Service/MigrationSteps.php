<?php
namespace Triadev\EsMigration\Business\Service;

use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;
use Triadev\EsMigration\Models\MigrationStep;

class MigrationSteps
{
    /** @var ElasticsearchMigrationContract */
    private $migrationRepository;
    
    /** @var ElasticsearchMigrationStepContract */
    private $migrationStepRepository;
    
    /**
     * MigrationSteps constructor.
     * @param ElasticsearchMigrationContract $migrationRepository
     * @param ElasticsearchMigrationStepContract $migrationStepRepository
     */
    public function __construct(
        ElasticsearchMigrationContract $migrationRepository,
        ElasticsearchMigrationStepContract $migrationStepRepository
    ) {
        $this->migrationRepository = $migrationRepository;
        $this->migrationStepRepository = $migrationStepRepository;
    }
    
    /**
     * Get migration steps
     *
     * @param string $migration
     * @param bool $withoutDoneSteps
     * @return MigrationStep[]
     */
    public function getMigrationSteps(string $migration, bool $withoutDoneSteps = true) : array
    {
        $migrations = [];
    
        if ($migrationEntity = $this->migrationRepository->find($migration)) {
            foreach ($migrationEntity->migrationSteps()->cursor() as $migrationStepEntity) {
                /** @var ElasticsearchMigrationStep $migrationStepEntity */
                if ($withoutDoneSteps &&
                    $migrationStepEntity->status == MigrationStatus::MIGRATION_STATUS_DONE) {
                    continue;
                }
                
                $migrations[] = $this->buildMigrationStep($migrationStepEntity);
            }
        }
    
        return $migrations;
    }
    
    /**
     * Get migration step
     *
     * @param int $migrationStepId
     * @return null|MigrationStep
     */
    public function getMigrationStep(int $migrationStepId) : ?MigrationStep
    {
        if ($migrationStepEntity = $this->migrationStepRepository->find($migrationStepId)) {
            return $this->buildMigrationStep($migrationStepEntity);
        }
        
        return null;
    }
    
    private function buildMigrationStep(ElasticsearchMigrationStep $elasticsearchMigrationStepEntity) : MigrationStep
    {
        return new MigrationStep(
            $elasticsearchMigrationStepEntity->id,
            $elasticsearchMigrationStepEntity->type,
            $elasticsearchMigrationStepEntity->status,
            $elasticsearchMigrationStepEntity->error,
            json_decode($elasticsearchMigrationStepEntity->params, true),
            $elasticsearchMigrationStepEntity->priority,
            $elasticsearchMigrationStepEntity->stop_on_failure,
            new \DateTime($elasticsearchMigrationStepEntity->created_at),
            new \DateTime($elasticsearchMigrationStepEntity->updated_at)
        );
    }
}
