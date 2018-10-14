<?php
namespace Tests\Integration\Console\Commands;

use Illuminate\Support\Carbon;
use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;

class ShowMigrationTest extends TestCase
{
    /** @var \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract */
    private $migrationRepository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->migrationRepository = app(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::class
        );
    }
    
    /**
     * @test
     */
    public function it_shows_migrations_with_status()
    {
        $now = Carbon::now()->subSeconds(1);
        
        Carbon::setTestNow($now);
        
        $this->migrationRepository->createOrUpdate(
            '1.0.0',
            ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE
        );
        
        $now->addSeconds(1);
    
        Carbon::setTestNow($now);
        
        $this->migrationRepository->createOrUpdate(
            '1.0.1',
            ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_ERROR
        );
        
        $this->artisan('triadev:elasticsearch:migration:show')
            ->assertExitCode(0);
    
        $this->artisan('triadev:elasticsearch:migration:show', [
            'sortField' => 'createdAt',
            'sortOrder' => 'desc'
        ])->assertExitCode(0);
    
        $this->artisan('triadev:elasticsearch:migration:show', [
            'sortField' => 'updatedAt',
            'sortOrder' => 'asc'
        ])->assertExitCode(0);
    }
}
