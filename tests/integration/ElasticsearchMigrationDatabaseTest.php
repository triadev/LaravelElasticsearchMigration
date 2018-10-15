<?php
namespace Tests\Integration;

use Tests\TestCase;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepAliasContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepCreateIndexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepDeleteByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepReindexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateIndexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex;
use Triadev\EsMigration\Models\Migrations\Alias;
use Triadev\EsMigration\Models\Migrations\CreateIndex;
use Triadev\EsMigration\Models\Migrations\DeleteByQuery;
use Triadev\EsMigration\Models\Migrations\DeleteIndex;
use Triadev\EsMigration\Models\Migrations\Reindex;
use Triadev\EsMigration\Models\Migrations\UpdateByQuery;
use Triadev\EsMigration\Models\Migrations\UpdateIndex;

class ElasticsearchMigrationDatabaseTest extends TestCase
{
    /** @var ElasticsearchMigrationDatabaseContract */
    private $service;
    
    /** @var ElasticsearchMigrationContract */
    private $elasticsearchMigrationRepository;
    
    /** @var ElasticsearchMigrationStepContract */
    private $elasticsearchMigrationStepRepository;
    
    public function setUp()
    {
        parent::setUp();
    
        $this->service = app(ElasticsearchMigrationDatabaseContract::class);
    
        $this->elasticsearchMigrationRepository = app(ElasticsearchMigrationContract::class);
        $this->elasticsearchMigrationStepRepository = app(ElasticsearchMigrationStepContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_migration()
    {
        $this->assertNull($this->elasticsearchMigrationRepository->find('1.0.0'));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertEquals(1, $this->elasticsearchMigrationRepository->find('1.0.0')->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_migration()
    {
        $this->assertFalse($this->service->addMigration('1.0.0', 'default', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
    
        $this->assertTrue($this->service->createMigration('1.0.0'));
    
        $this->assertTrue($this->service->addMigration('1.0.0', 'createIndex', 'phpunit', [
            'mappings' => [
                'example' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]));
        
        $this->assertEquals(
            1,
            $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id')
        );
    }
    
    /**
     * @test
     */
    public function it_adds_create_index_migration()
    {
        /** @var ElasticsearchMigrationStepCreateIndexContract $repository */
        $repository = app(ElasticsearchMigrationStepCreateIndexContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'createIndex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'createIndex', 'phpunit', [
            'mappings' => []
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationStepCreateIndex::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_update_index_migration()
    {
        /** @var ElasticsearchMigrationStepUpdateIndexContract $repository */
        $repository = app(ElasticsearchMigrationStepUpdateIndexContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit', []));
        
        $this->assertEquals(1, $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id'));
    
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationStepUpdateIndex::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_delete_index_migration()
    {
        $this->assertFalse($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit', []));
        
        $this->assertEquals(1, $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_alias_migration()
    {
        /** @var ElasticsearchMigrationStepAliasContract $repository */
        $repository = app(ElasticsearchMigrationStepAliasContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'alias', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'alias', 'phpunit', []));
        
        $this->assertEquals(1, $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationStepAlias::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_delete_by_query_migration()
    {
        /** @var ElasticsearchMigrationStepDeleteByQueryContract $repository */
        $repository = app(ElasticsearchMigrationStepDeleteByQueryContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'deleteByQuery', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteByQuery', 'phpunit', [
            'query' => []
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationStepDeleteByQuery::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_update_by_query_migration()
    {
        /** @var ElasticsearchMigrationStepUpdateByQueryContract $repository */
        $repository = app(ElasticsearchMigrationStepUpdateByQueryContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'updateByQuery', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateByQuery', 'phpunit', [
            'query' => []
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationStepUpdateByQuery::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_reindex_migration()
    {
        /** @var ElasticsearchMigrationStepReindexContract $repository */
        $repository = app(ElasticsearchMigrationStepReindexContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'reindex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationStepRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'reindex', 'phpunit', [
            'destIndex' => 'phpunit'
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationStepRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationStepReindex::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
        $this->assertEquals('phpunit', $migration->getAttribute('dest_index'));
    }
    
    /**
     * @test
     */
    public function it_gets_migration()
    {
        $this->assertEquals([], $this->service->getMigration('1.0.0'));
    
        $this->assertTrue($this->service->createMigration('1.0.0'));
        $this->assertFalse($this->service->addMigration('1.0.0', 'default', 'phpunit'));
        $this->assertTrue($this->service->addMigration('1.0.0', 'createIndex', 'phpunit', [
            'mappings' => [
                'example' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]));
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'alias', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteByQuery', 'phpunit', [
            'query' => []
        ]));
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateByQuery', 'phpunit', [
            'query' => []
        ]));
        $this->assertTrue($this->service->addMigration('1.0.0', 'reindex', 'phpunit', [
            'destIndex' => 'phpunit'
        ]));
        
        $migrations = $this->service->getMigration('1.0.0');
        
        $this->assertCount(7, $migrations);
        
        $this->assertInstanceOf(CreateIndex::class, $migrations[1]);
        $this->assertInstanceOf(UpdateIndex::class, $migrations[2]);
        $this->assertInstanceOf(DeleteIndex::class, $migrations[3]);
        $this->assertInstanceOf(Alias::class, $migrations[4]);
        $this->assertInstanceOf(DeleteByQuery::class, $migrations[5]);
        $this->assertInstanceOf(UpdateByQuery::class, $migrations[6]);
        $this->assertInstanceOf(Reindex::class, $migrations[7]);
    }
    
    /**
     * @test
     */
    public function it_gets_migration_without_status_done_steps()
    {
        $this->assertEquals([], $this->service->getMigration('1.0.0'));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        $this->assertFalse($this->service->addMigration('1.0.0', 'default', 'phpunit'));
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'alias', 'phpunit', []));
        
        $this->assertCount(3, $this->service->getMigration('1.0.0'));
        
        $this->elasticsearchMigrationStepRepository->update(
            1,
            ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE,
            null
        );
    
        $this->assertCount(2, $this->service->getMigration('1.0.0'));
    }
    
    /**
     * @test
     */
    public function it_gets_migration_steps()
    {
        $this->assertEquals([], $this->service->getMigration('1.0.0'));
    
        $this->assertTrue($this->service->createMigration('1.0.0'));
        $this->assertFalse($this->service->addMigration('1.0.0', 'default', 'phpunit'));
        $this->assertTrue($this->service->addMigration('1.0.0', 'createIndex', 'phpunit', [
            'mappings' => [
                'example' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ]));
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'alias', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteByQuery', 'phpunit', [
            'query' => []
        ]));
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateByQuery', 'phpunit', [
            'query' => []
        ]));
        $this->assertTrue($this->service->addMigration('1.0.0', 'reindex', 'phpunit', [
            'destIndex' => 'phpunit'
        ]));
        
        $migrationSteps = $this->service->getMigrationSteps('1.0.0');
        $this->assertCount(7, $migrationSteps);
        
        foreach ($migrationSteps as $migrationStep) {
            $this->assertTrue(array_has(
                $migrationStep,
                [
                    'type',
                    'index',
                    'status',
                    'error',
                    'created_at',
                    'updated_at'
                ]
            ));
        }
    }
    
    /**
     * @test
     */
    public function it_gets_migration_steps_without_status_done_steps()
    {
        $this->assertEquals([], $this->service->getMigration('1.0.0'));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        $this->assertFalse($this->service->addMigration('1.0.0', 'default', 'phpunit'));
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit', []));
        $this->assertTrue($this->service->addMigration('1.0.0', 'alias', 'phpunit', []));
        
        $this->assertCount(3, $this->service->getMigrationSteps('1.0.0'));
    
        $this->elasticsearchMigrationStepRepository->update(
            1,
            ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE,
            null
        );
    
        $this->assertCount(2, $this->service->getMigrationSteps('1.0.0'));
    }
}
