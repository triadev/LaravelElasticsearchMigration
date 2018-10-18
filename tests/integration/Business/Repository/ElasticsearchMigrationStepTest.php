<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Business\Events\MigrationStepDone;
use Triadev\EsMigration\Business\Events\MigrationStepError;
use Triadev\EsMigration\Business\Events\MigrationStepRunning;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;

class ElasticsearchMigrationStepTest extends TestCase
{
    /** @var ElasticsearchMigrationStepContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationStepContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'createIndex', [
            'index' => 'phpunit'
        ]);
        
        $this->repository->create(2, 'createIndex', [
            'index' => 'phpunit'
        ]);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationStep::class,
            $this->repository->find(1)
        );
        
        $this->assertEquals(2, ElasticsearchMigrationStep::where('migration_id', '=', 2)->count());
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
        
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'createIndex', [
            'index' => 'phpunit'
        ]);
        $this->assertEquals(MigrationStatus::MIGRATION_STATUS_WAIT, $this->repository->find(1)->status);
    
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
        $this->repository->create(2, 'createIndex', [
            'index' => 'phpunit'
        ]);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationStep::class,
            $this->repository->find(1)
        );
    }
}
