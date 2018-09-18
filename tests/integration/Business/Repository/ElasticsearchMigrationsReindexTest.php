<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsReindexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex;

class ElasticsearchMigrationsReindexTest extends TestCase
{
    /** @var ElasticsearchMigrationsReindexContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationsReindexContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(1, 'destIndex', false, [], [], []);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationsReindex::class,
            $this->repository->find(1)
        );
    }
    
    /**
     * @test
     * @expectedException \Illuminate\Database\QueryException
     */
    public function it_expected_a_sql_unique_exception()
    {
        $this->assertNull($this->repository->find(1));
    
        $this->repository->create(1, 'destIndex', false, [], [], []);
        $this->repository->create(1, 'destIndex', false, [], [], []);
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $this->repository->create(1, 'destIndex', false, [], [], []);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationsReindex::class,
            $this->repository->find(1)
        );
    }
}
