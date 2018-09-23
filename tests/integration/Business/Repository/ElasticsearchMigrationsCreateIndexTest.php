<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsCreateIndexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex;

class ElasticsearchMigrationsCreateIndexTest extends TestCase
{
    /** @var ElasticsearchMigrationsCreateIndexContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationsCreateIndexContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(1, [], null);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationsCreateIndex::class,
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
        
        $this->repository->create(1, [], null);
        $this->repository->create(1, [], null);
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $this->repository->create(1, [], null);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationsCreateIndex::class,
            $this->repository->find(1)
        );
    }
}
