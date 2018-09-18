<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
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
}
