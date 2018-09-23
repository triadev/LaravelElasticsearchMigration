<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsAliasContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsCreateIndexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsDeleteByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsReindexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateIndexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrations;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsAlias;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsCreateIndex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsDeleteByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsUpdateIndex;

class ElasticsearchMigrationsTest extends TestCase
{
    /** @var ElasticsearchMigrationsContract */
    private $repository;
    
    /** @var ElasticsearchMigrationsCreateIndexContract */
    private $createIndexRepository;
    
    /** @var ElasticsearchMigrationsUpdateIndexContract */
    private $updateIndexRepository;
    
    /** @var ElasticsearchMigrationsAliasContract */
    private $aliasRepository;
    
    /** @var ElasticsearchMigrationsDeleteByQueryContract */
    private $deleteByQueryRepository;
    
    /** @var ElasticsearchMigrationsUpdateByQueryContract */
    private $updateByQueryRepository;
    
    /** @var ElasticsearchMigrationsReindexContract */
    private $reindexRepository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationsContract::class);
        $this->createIndexRepository = app(ElasticsearchMigrationsCreateIndexContract::class);
        $this->updateIndexRepository = app(ElasticsearchMigrationsUpdateIndexContract::class);
        $this->aliasRepository = app(ElasticsearchMigrationsAliasContract::class);
        $this->deleteByQueryRepository = app(ElasticsearchMigrationsDeleteByQueryContract::class);
        $this->updateByQueryRepository = app(ElasticsearchMigrationsUpdateByQueryContract::class);
        $this->reindexRepository = app(ElasticsearchMigrationsReindexContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'createIndex', 'phpunit');
        $this->repository->create(2, 'createIndex', 'phpunit');
        
        $this->assertInstanceOf(
            ElasticsearchMigrations::class,
            $this->repository->find(1)
        );
        
        $this->assertEquals(2, ElasticsearchMigrations::where('migration_id', '=', 2)->count());
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $this->repository->create(2, 'createIndex', 'phpunit');
        
        $this->assertInstanceOf(
            ElasticsearchMigrations::class,
            $this->repository->find(1)
        );
    }
    
    /**
     * @test
     */
    public function it_gets_create_index_migration()
    {
        $this->assertNull($this->repository->find(1));
    
        $this->repository->create(2, 'createIndex', 'phpunit');
    
        $this->createIndexRepository->create(1, [], null);
        
        $migrationByType = $this->repository->find(1)->migrationByType();
        
        $this->assertEquals(1, $migrationByType->count());
        $this->assertEquals(
            ElasticsearchMigrationsCreateIndex::class,
            get_class($migrationByType->first())
        );
    }
    
    /**
     * @test
     */
    public function it_gets_update_index_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'updateIndex', 'phpunit');
        
        $this->updateIndexRepository->create(1, [], null);
        
        $migrationByType = $this->repository->find(1)->migrationByType();
        
        $this->assertEquals(1, $migrationByType->count());
        $this->assertEquals(
            ElasticsearchMigrationsUpdateIndex::class,
            get_class($migrationByType->first())
        );
    }
    
    /**
     * @test
     */
    public function it_gets_delete_index_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'deleteIndex', 'phpunit');
        
        $this->assertEquals('deleteIndex', $this->repository->find(1)->migrationByType());
    }
    
    /**
     * @test
     */
    public function it_gets_alias_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'alias', 'phpunit');
        
        $this->aliasRepository->create(1, [], [], []);
        
        $migrationByType = $this->repository->find(1)->migrationByType();
        
        $this->assertEquals(1, $migrationByType->count());
        $this->assertEquals(
            ElasticsearchMigrationsAlias::class,
            get_class($migrationByType->first())
        );
    }
    
    /**
     * @test
     */
    public function it_gets_delete_by_query_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'deleteByQuery', 'phpunit');
        
        $this->deleteByQueryRepository->create(1, [], null, []);
        
        $migrationByType = $this->repository->find(1)->migrationByType();
        
        $this->assertEquals(1, $migrationByType->count());
        $this->assertEquals(
            ElasticsearchMigrationsDeleteByQuery::class,
            get_class($migrationByType->first())
        );
    }
    
    /**
     * @test
     */
    public function it_gets_update_by_query_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'updateByQuery', 'phpunit');
        
        $this->updateByQueryRepository->create(1, [], null, null, []);
        
        $migrationByType = $this->repository->find(1)->migrationByType();
        
        $this->assertEquals(1, $migrationByType->count());
        $this->assertEquals(
            ElasticsearchMigrationsUpdateByQuery::class,
            get_class($migrationByType->first())
        );
    }
    
    /**
     * @test
     */
    public function it_gets_reindex_migration()
    {
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'reindex', 'phpunit');
        
        $this->reindexRepository->create(1, 'dest_index', false, [], [], []);
        
        $migrationByType = $this->repository->find(1)->migrationByType();
        
        $this->assertEquals(1, $migrationByType->count());
        $this->assertEquals(
            ElasticsearchMigrationsReindex::class,
            get_class($migrationByType->first())
        );
    }
}
