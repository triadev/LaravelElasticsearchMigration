<?php
namespace Tests\Integration\Console\Commands;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class ShowMigrationTest extends TestCase
{
    /** @var \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract */
    private $migrationRepository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->migrationRepository = app(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract::class
        );
    }
    
    /**
     * @test
     */
    public function it_shows_migrations_with_status()
    {
        $now = Carbon::now()->subSeconds(1);
        
        Carbon::setTestNow($now);
        
        $this->migrationRepository->createOrUpdate('1.0.0', 'done');
        
        $now->addSeconds(1);
    
        Carbon::setTestNow($now);
        
        $this->migrationRepository->createOrUpdate('1.0.1', 'error');
        
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
