<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepReindexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex;

class ElasticsearchMigrationStepReindexTest extends TestCase
{
    /** @var ElasticsearchMigrationStepReindexContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationStepReindexContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(1, 'destIndex', false, [], [], []);
        
        $this->assertInstanceOf(
            ElasticsearchMigrationStepReindex::class,
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
            ElasticsearchMigrationStepReindex::class,
            $this->repository->find(1)
        );
    }
}
