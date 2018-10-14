<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Business\Events\MigrationDone;
use Triadev\EsMigration\Business\Events\MigrationError;
use Triadev\EsMigration\Business\Events\MigrationRunning;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

class ElasticsearchMigrationTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $repository;
    
    /** @var ElasticsearchMigrationsContract */
    private $migrationsRepository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationContract::class);
        $this->migrationsRepository = app(ElasticsearchMigrationsContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find('1.0.0'));
        
        $this->repository->createOrUpdate('1.0.0');
    
        $this->assertInstanceOf(
            ElasticsearchMigration::class,
            $this->repository->find('1.0.0')
        );
    }
    
    /**
     * @test
     */
    public function it_updates_a_migration()
    {
        $this->expectsEvents([
            MigrationRunning::class,
            MigrationError::class,
            MigrationDone::class
        ]);
        
        // WAIT
        $this->repository->createOrUpdate('1.0.0');
        $this->assertEquals(ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_WAIT, $this->repository->find('1.0.0')->status);
    
        // ERROR
        $this->repository->createOrUpdate('1.0.0', ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_ERROR);
        $this->assertEquals(ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_ERROR, $this->repository->find('1.0.0')->status);
        
        // RUNNING
        $this->repository->createOrUpdate('1.0.0', ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_RUNNING);
        $this->assertEquals(ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_RUNNING, $this->repository->find('1.0.0')->status);
        
        // DONE
        $this->repository->createOrUpdate('1.0.0', ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE);
        $this->assertEquals(ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE, $this->repository->find('1.0.0')->status);
    
        // DONE => invalid status id
        $this->repository->createOrUpdate('1.0.0', 999);
        $this->assertEquals(ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE, $this->repository->find('1.0.0')->status);
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $this->repository->createOrUpdate('1.0.0');
        
        $this->assertInstanceOf(
            ElasticsearchMigration::class,
            $this->repository->find('1.0.0')
        );
    }
    
    /**
     * @test
     */
    public function it_deletes_a_migration()
    {
        $this->repository->createOrUpdate('1.0.0');
        
        $this->assertInstanceOf(
            ElasticsearchMigration::class,
            $this->repository->find('1.0.0')
        );
        
        $this->repository->delete('1.0.0');
    
        $this->assertNull($this->repository->find('1.0.0'));
    }
    
    /**
     * @test
     */
    public function it_gets_many_migrations()
    {
        $this->repository->createOrUpdate('1.0.0');
    
        $this->assertInstanceOf(
            ElasticsearchMigration::class,
            $this->repository->find('1.0.0')
        );
    
        $this->migrationsRepository->create(1, 'create', 'phpunit');
        $this->migrationsRepository->create(1, 'update', 'phpunit');
        
        $migration = $this->repository->find('1.0.0');
        
        $this->assertEquals(2, $migration->migrations()->count());
    }
    
    /**
     * @test
     */
    public function it_gets_all_migrations()
    {
        $this->assertEmpty($this->repository->all());
    
        $this->repository->createOrUpdate('1.0.0');
        $this->repository->createOrUpdate('1.0.1');
        
        $migrations = $this->repository->all(['id', 'migration', 'status']);
        
        $this->assertEquals('1.0.0', $migrations[0]->migration);
        $this->assertEquals(ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_WAIT, $migrations[0]->status);
        
        $this->assertEquals('1.0.1', $migrations[1]->migration);
        $this->assertEquals(ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_WAIT, $migrations[1]->status);
    }
}
