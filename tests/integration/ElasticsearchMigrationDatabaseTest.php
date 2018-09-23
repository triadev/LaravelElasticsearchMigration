<?php
namespace Tests\Integration;

use Tests\TestCase;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsAliasContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsCreateIndexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsDeleteByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsReindexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateIndexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex;
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
    
    /** @var ElasticsearchMigrationsContract */
    private $elasticsearchMigrationsRepository;
    
    public function setUp()
    {
        parent::setUp();
    
        $this->service = app(ElasticsearchMigrationDatabaseContract::class);
    
        $this->elasticsearchMigrationRepository = app(ElasticsearchMigrationContract::class);
        $this->elasticsearchMigrationsRepository = app(ElasticsearchMigrationsContract::class);
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
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
    
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'default', 'phpunit'));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_create_index_migration()
    {
        /** @var ElasticsearchMigrationsCreateIndexContract $repository */
        $repository = app(ElasticsearchMigrationsCreateIndexContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'createIndex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'createIndex', 'phpunit', [
            'mappings' => []
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationsCreateIndex::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_update_index_migration()
    {
        /** @var ElasticsearchMigrationsUpdateIndexContract $repository */
        $repository = app(ElasticsearchMigrationsUpdateIndexContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateIndex', 'phpunit', []));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
    
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationsUpdateIndex::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_delete_index_migration()
    {
        $this->assertFalse($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteIndex', 'phpunit', []));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_alias_migration()
    {
        /** @var ElasticsearchMigrationsAliasContract $repository */
        $repository = app(ElasticsearchMigrationsAliasContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'alias', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'alias', 'phpunit', []));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationsAlias::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_delete_by_query_migration()
    {
        /** @var ElasticsearchMigrationsDeleteByQueryContract $repository */
        $repository = app(ElasticsearchMigrationsDeleteByQueryContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'deleteByQuery', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'deleteByQuery', 'phpunit', [
            'query' => []
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationsDeleteByQuery::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_update_by_query_migration()
    {
        /** @var ElasticsearchMigrationsUpdateByQueryContract $repository */
        $repository = app(ElasticsearchMigrationsUpdateByQueryContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'updateByQuery', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'updateByQuery', 'phpunit', [
            'query' => []
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationsUpdateByQuery::class, $migration);
        $this->assertEquals(1, $migration->getAttribute('id'));
    }
    
    /**
     * @test
     */
    public function it_adds_reindex_migration()
    {
        /** @var ElasticsearchMigrationsReindexContract $repository */
        $repository = app(ElasticsearchMigrationsReindexContract::class);
        
        $this->assertFalse($this->service->addMigration('1.0.0', 'reindex', 'phpunit'));
        $this->assertNull($this->elasticsearchMigrationsRepository->find(1));
        $this->assertNull($repository->find(1));
        
        $this->assertTrue($this->service->createMigration('1.0.0'));
        
        $this->assertTrue($this->service->addMigration('1.0.0', 'reindex', 'phpunit', [
            'destIndex' => 'phpunit'
        ]));
        
        $this->assertEquals(1, $this->elasticsearchMigrationsRepository->find(1)->getAttribute('id'));
        
        $migration = $repository->find(1);
        $this->assertInstanceOf(ElasticsearchMigrationsReindex::class, $migration);
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
        $this->assertTrue($this->service->addMigration('1.0.0', 'default', 'phpunit'));
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
        
        $this->assertInstanceOf(CreateIndex::class, $migrations[0]);
        $this->assertInstanceOf(UpdateIndex::class, $migrations[1]);
        $this->assertInstanceOf(DeleteIndex::class, $migrations[2]);
        $this->assertInstanceOf(Alias::class, $migrations[3]);
        $this->assertInstanceOf(DeleteByQuery::class, $migrations[4]);
        $this->assertInstanceOf(UpdateByQuery::class, $migrations[5]);
        $this->assertInstanceOf(Reindex::class, $migrations[6]);
    }
}
