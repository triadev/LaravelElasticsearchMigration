<?php
namespace Tests\Integration\Console\Commands;

use Tests\TestCase;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;

class ImportFileMigrationsTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $repositoryMigrations;
    
    /** @var ElasticsearchMigrationStepContract */
    private $repositoryMigrationSteps;
    
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->repositoryMigrations = app(ElasticsearchMigrationContract::class);
        $this->repositoryMigrationSteps = app(ElasticsearchMigrationStepContract::class);
    }
    
    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage No migration file path was defined.
     */
    public function it_throws_an_exception_if_no_migration_file_path_was_defined()
    {
        $this->artisan('triadev:es-migration:import-file-migrations', [
            'migration' => 'phpunit',
            'filePath' => 'phpunit'
        ]);
    }
    
    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage The migration directory does not exist.
     */
    public function it_throws_an_exception_if_migration_directory_not_exist()
    {
        config()->set('triadev-elasticsearch-migration.filePath', [
            'phpunit' => __DIR__ . '/Migrations'
        ]);
        
        $this->artisan('triadev:es-migration:import-file-migrations', [
            'migration' => 'not_found',
            'filePath' => 'phpunit'
        ]);
    }
    
    /**
     * @test
     */
    public function it_imports_file_migrations()
    {
        $this->assertNull($this->repositoryMigrations->find('phpunit'));
        
        $this->assertNull($this->repositoryMigrationSteps->find(1));
        $this->assertNull($this->repositoryMigrationSteps->find(2));
        
        config()->set('triadev-elasticsearch-migration.filePath', [
            'phpunit' => __DIR__ . '/Migrations'
        ]);
        
        $this->artisan('triadev:es-migration:import-file-migrations', [
            'migration' => 'phpunit',
            'filePath' => 'phpunit'
        ]);
    
        $this->assertNotNull($this->repositoryMigrations->find('phpunit'));
    
        $this->assertNotNull($this->repositoryMigrationSteps->find(1));
        $this->assertNotNull($this->repositoryMigrationSteps->find(2));
    }
}
