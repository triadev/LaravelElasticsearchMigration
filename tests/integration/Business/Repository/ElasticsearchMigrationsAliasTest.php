<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsAliasContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias;

class ElasticsearchMigrationsAliasTest extends TestCase
{
    /** @var ElasticsearchMigrationsAliasContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationsAliasContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(1, [], [], []);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationsAlias::class,
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
    
        $this->repository->create(1, [], [], []);
        $this->repository->create(1, [], [], []);
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $this->repository->create(1, [], [], []);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationsAlias::class,
            $this->repository->find(1)
        );
    }
}