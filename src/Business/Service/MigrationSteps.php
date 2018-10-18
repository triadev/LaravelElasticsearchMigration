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
            foreach ($migrationEntity->migrationSteps()->getResults() as $migrationStepEntity) {
                /** @var ElasticsearchMigrationStep $migrationStepEntity */
                if ($withoutDoneSteps &&
                    $migrationStepEntity->status == MigrationStatus::MIGRATION_STATUS_DONE) {
                    continue;
                }
                
                $migrations[] = new MigrationStep(
                    $migrationStepEntity->id,
                    $migrationStepEntity->type,
                    $migrationStepEntity->status,
                    $migrationStepEntity->error,
                    json_decode($migrationStepEntity->params, true),
                    new \DateTime($migrationStepEntity->created_at),
                    new \DateTime($migrationStepEntity->updated_at)
                );
            }
        }
    
        return $migrations;
    }
}
