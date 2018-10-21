<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepAuditLogContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAuditLog;

class ElasticsearchMigrationStepAuditLogTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $repositoryMigration;
    
    /** @var ElasticsearchMigrationStepContract */
    private $repositoryMigrationStep;
    
    /** @var ElasticsearchMigrationStepAuditLogContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repositoryMigration = app(ElasticsearchMigrationContract::class);
        $this->repositoryMigrationStep = app(ElasticsearchMigrationStepContract::class);
        $this->repository = app(ElasticsearchMigrationStepAuditLogContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertEquals(0, ElasticsearchMigrationStepAuditLog::all()->count());
        
        $migration = $this->repositoryMigration->createOrUpdate('phpunit');
        
        $migrationStep = $this->repositoryMigrationStep->create(
            $migration->id,
            MigrationTypes::MIGRATION_TYPE_CREATE_INDEX,
            [],
            1,
            true
        );
        
        $this->assertInstanceOf(
            ElasticsearchMigrationStepAuditLog::class,
            $this->repository->create(
                $migrationStep->id,
                MigrationStatus::MIGRATION_STATUS_RUNNING
            )
        );
    
        $this->assertInstanceOf(
            ElasticsearchMigrationStepAuditLog::class,
            $this->repository->create(
                $migrationStep->id,
                MigrationStatus::MIGRATION_STATUS_DONE
            )
        );
    
        $this->assertEquals(2, ElasticsearchMigrationStepAuditLog::all()->count());
    }
}
