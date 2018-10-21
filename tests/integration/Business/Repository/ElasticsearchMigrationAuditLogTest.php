<?php
namespace Tests\Integration\Business\Repository;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationAuditLogContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationAuditLog;

class ElasticsearchMigrationAuditLogTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $repositoryMigration;
    
    /** @var ElasticsearchMigrationAuditLogContract */
    private $repository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->repositoryMigration = app(ElasticsearchMigrationContract::class);
        $this->repository = app(ElasticsearchMigrationAuditLogContract::class);
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertEquals(0, ElasticsearchMigrationAuditLog::all()->count());
        
        $migration = $this->repositoryMigration->createOrUpdate('phpunit');
        
        $this->assertInstanceOf(
            ElasticsearchMigrationAuditLog::class,
            $this->repository->create(
                $migration->id,
                MigrationStatus::MIGRATION_STATUS_RUNNING
            )
        );
    
        $this->assertInstanceOf(
            ElasticsearchMigrationAuditLog::class,
            $this->repository->create(
                $migration->id,
                MigrationStatus::MIGRATION_STATUS_DONE
            )
        );
    
        $this->assertEquals(2, ElasticsearchMigrationAuditLog::all()->count());
    }
}
