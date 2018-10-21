<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Business\Events\MigrationStepDone;
use Triadev\EsMigration\Business\Events\MigrationStepError;
use Triadev\EsMigration\Business\Events\MigrationStepRunning;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;

class ElasticsearchMigrationStepTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $repositoryMigration;
    
    /** @var ElasticsearchMigrationStepContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repositoryMigration = app(ElasticsearchMigrationContract::class);
        $this->repository = app(ElasticsearchMigrationStepContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $migration = $this->repositoryMigration->createOrUpdate('phpunit');
        
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create($migration->id, 'createIndex', [
            'index' => 'phpunit'
        ], 2);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationStep::class,
            $this->repository->find(1)
        );
        
        $this->assertEquals(
            1,
            ElasticsearchMigrationStep::where('migration_id', '=', $migration->id)->count()
        );
        
        $migrationStep = $this->repository->find(1);
        $this->assertEquals(MigrationStatus::MIGRATION_STATUS_WAIT, $migrationStep->status);
        $this->assertEquals(2, $migrationStep->priority);
    }
    
    /**
     * @test
     */
    public function it_updates_a_migration()
    {
        $this->expectsEvents([
            MigrationStepRunning::class,
            MigrationStepError::class,
            MigrationStepDone::class
        ]);
    
        $migration = $this->repositoryMigration->createOrUpdate('phpunit');
        
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create($migration->id, 'createIndex', [
            'index' => 'phpunit'
        ]);
        $this->assertEquals(MigrationStatus::MIGRATION_STATUS_WAIT, $this->repository->find(1)->status);
    
        // Priority
        $this->repository->update(1, MigrationStatus::MIGRATION_STATUS_WAIT, null, 2);
        $this->assertEquals(2, $this->repository->find(1)->priority);
        
        // Running
        $this->repository->update(1, MigrationStatus::MIGRATION_STATUS_RUNNING);
        $this->assertEquals(MigrationStatus::MIGRATION_STATUS_RUNNING, $this->repository->find(1)->status);
    
        // Error
        $this->repository->update(1, MigrationStatus::MIGRATION_STATUS_ERROR, 'error');
        $this->assertEquals(MigrationStatus::MIGRATION_STATUS_ERROR, $this->repository->find(1)->status);
        $this->assertEquals('error', $this->repository->find(1)->error);
        
        // Done
        $this->repository->update(1, MigrationStatus::MIGRATION_STATUS_DONE);
        $this->assertEquals(MigrationStatus::MIGRATION_STATUS_DONE, $this->repository->find(1)->status);
    
        // Done => invalid status id
        $this->repository->update(1, 999);
        $this->assertEquals(MigrationStatus::MIGRATION_STATUS_DONE, $this->repository->find(1)->status);
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationsNotExist
     */
    public function it_throws_an_exception_when_migrations_not_exist_at_update()
    {
        $this->repository->update(1, MigrationStatus::MIGRATION_STATUS_DONE);
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $migration = $this->repositoryMigration->createOrUpdate('phpunit');
        
        $migrationStep = $this->repository->create($migration->id, 'createIndex', [
            'index' => 'phpunit'
        ]);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationStep::class,
            $this->repository->find($migrationStep->id)
        );
    }
    
    /**
     * @test
     */
    public function it_deletes_a_migration()
    {
        $migration = $this->repositoryMigration->createOrUpdate('phpunit');
        
        $this->repository->create($migration->id, 'createIndex', [
            'index' => 'phpunit'
        ]);
    
        $this->assertInstanceOf(ElasticsearchMigrationStep::class, $this->repository->find(1));
        
        $this->repository->delete(1);
        
        $this->assertNull($this->repository->find(1));
    }
}
