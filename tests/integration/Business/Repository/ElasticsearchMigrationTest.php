<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

class ElasticsearchMigrationTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find('1.0.0'));
        
        $this->repository->createOrUpdate('1.0.0', 'done');
    
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
        
        $this->repository->createOrUpdate('1.0.0', 'done');
        
        $this->assertEquals('done', $this->repository->find('1.0.0')->status);
    
        $this->repository->createOrUpdate('1.0.0', 'error');
    
        $this->assertEquals('error', $this->repository->find('1.0.0')->status);
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $this->repository->createOrUpdate('1.0.0', 'done');
    
        $migration = $this->repository->find('1.0.0');
        
        $this->assertEquals('1.0.0', $migration->migration);
        $this->assertEquals('done', $migration->status);
    }
    
    /**
     * @test
     */
    public function it_deletes_a_migration()
    {
        $this->repository->createOrUpdate('1.0.0', 'done');
        
        $this->assertInstanceOf(
            ElasticsearchMigration::class,
            $this->repository->find('1.0.0')
        );
        
        $this->repository->delete('1.0.0');
    
        $this->assertNull($this->repository->find('1.0.0'));
    }
}
