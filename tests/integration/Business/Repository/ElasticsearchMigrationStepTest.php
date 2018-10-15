<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Business\Events\MigrationStepDone;
use Triadev\EsMigration\Business\Events\MigrationStepError;
use Triadev\EsMigration\Business\Events\MigrationStepRunning;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepAliasContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepCreateIndexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepDeleteByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepReindexContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateByQueryContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepUpdateIndexContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStep;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepAlias;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepCreateIndex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepDeleteByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepReindex;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateByQuery;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationStepUpdateIndex;

class ElasticsearchMigrationStepTest extends TestCase
{
    /** @var ElasticsearchMigrationStepContract */
    private $repository;
    
    /** @var ElasticsearchMigrationStepCreateIndexContract */
    private $createIndexRepository;
    
    /** @var ElasticsearchMigrationStepUpdateIndexContract */
    private $updateIndexRepository;
    
    /** @var ElasticsearchMigrationStepAliasContract */
    private $aliasRepository;
    
    /** @var ElasticsearchMigrationStepDeleteByQueryContract */
    private $deleteByQueryRepository;
    
    /** @var ElasticsearchMigrationStepUpdateByQueryContract */
    private $updateByQueryRepository;
    
    /** @var ElasticsearchMigrationStepReindexContract */
    private $reindexRepository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repository = app(ElasticsearchMigrationStepContract::class);
        $this->createIndexRepository = app(ElasticsearchMigrationStepCreateIndexContract::class);
        $this->updateIndexRepository = app(ElasticsearchMigrationStepUpdateIndexContract::class);
        $this->aliasRepository = app(ElasticsearchMigrationStepAliasContract::class);
        $this->deleteByQueryRepository = app(ElasticsearchMigrationStepDeleteByQueryContract::class);
        $this->updateByQueryRepository = app(ElasticsearchMigrationStepUpdateByQueryContract::class);
        $this->reindexRepository = app(ElasticsearchMigrationStepReindexContract::class);
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
            ElasticsearchMigrationStep::class,
            $this->repository->find(1)
        );
        
        $this->assertEquals(2, ElasticsearchMigrationStep::where('migration_id', '=', 2)->count());
    }
    
    /**
     * @test
     */
    public function it_updates_a_migration()
    {
        $this->expectsEvents([
            MigrationStepRunning::class,
            MigrationStepError::class,
            MigrationStepDone::class
        ]);
        
        $this->assertNull($this->repository->find(1));
        
        $this->repository->create(2, 'createIndex', 'phpunit');
        $this->assertEquals(ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_WAIT, $this->repository->find(1)->status);
    
        // Running
        $this->repository->update(1, ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_RUNNING);
        $this->assertEquals(ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_RUNNING, $this->repository->find(1)->status);
    
        // Error
        $this->repository->update(1, ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_ERROR, 'error');
        $this->assertEquals(ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_ERROR, $this->repository->find(1)->status);
        $this->assertEquals('error', $this->repository->find(1)->error);
        
        // Done
        $this->repository->update(1, ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE);
        $this->assertEquals(ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE, $this->repository->find(1)->status);
    
        // Done => invalid status id
        $this->repository->update(1, 999);
        $this->assertEquals(ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE, $this->repository->find(1)->status);
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationsNotExist
     */
    public function it_throws_an_exception_when_migrations_not_exist_at_update()
    {
        $this->repository->update(1, ElasticsearchMigrationStepContract::ELASTICSEARCH_MIGRATION_STEP_STATUS_DONE);
    }
    
    /**
     * @test
     */
    public function it_finds_a_migration()
    {
        $this->repository->create(2, 'createIndex', 'phpunit');
        
        $this->assertInstanceOf(
            ElasticsearchMigrationStep::class,
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
            ElasticsearchMigrationStepCreateIndex::class,
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
            ElasticsearchMigrationStepUpdateIndex::class,
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
            ElasticsearchMigrationStepAlias::class,
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
            ElasticsearchMigrationStepDeleteByQuery::class,
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
            ElasticsearchMigrationStepUpdateByQuery::class,
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
            ElasticsearchMigrationStepReindex::class,
            get_class($migrationByType->first())
        );
    }
}
