<?php
namespace Tests\Integration\Business\Service;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Service\MigrationSteps;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract;

class MigrationStepsTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $migrationService;
    
    /** @var MigrationSteps */
    private $migrationStepsService;
    
    /** @var ElasticsearchMigrationStepContract */
    private $migrationStepRepository;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->migrationService = app(ElasticsearchMigrationContract::class);
        $this->migrationStepsService = app(MigrationSteps::class);
        $this->migrationStepRepository = app(ElasticsearchMigrationStepContract::class);
    }
    
    /**
     * @test
     */
    public function it_gets_migration_steps_without_done_steps()
    {
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
        
        $this->addMigrationSteps();
        
        $this->assertCount(
            7,
            $this->migrationStepsService->getMigrationSteps('phpunit', true)
        );
        
        $this->migrationStepRepository->update(3, MigrationStatus::MIGRATION_STATUS_DONE, null);
    
        $this->assertCount(
            7,
            $this->migrationStepsService->getMigrationSteps('phpunit', false)
        );
    
        $result = $this->migrationStepsService->getMigrationSteps('phpunit', true);
        $this->assertCount(6, $result);
    }
    
    private function addMigrationSteps()
    {
        // Create index
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_CREATE_INDEX,
            [
                'index' => 'index',
                'body' => [
                    'mappings' => [
                        'phpunit' => [
                            'dynamic' => 'strict',
                            'properties' => [
                                'title' => [
                                    'type' => 'text'
                                ],
                                'count' => [
                                    'type' => 'integer'
                                ]
                            ]
                        ]
                    ],
                    'settings' => [
                        'refresh_interval' => "30s"
                    ]
                ]
            ]
        ));
        
        // Update index
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            [
                'index' => 'index'
            ]
        ));
        
        // Delete index
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_INDEX,
            [
                'index' => 'index'
            ]
        ));
        
        // Alias
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_PUT_ALIAS,
            [
                'index' => 'index'
            ]
        ));
        
        // Delete by query
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY,
            [
                'index' => 'index',
                'query' => []
            ]
        ));
        
        // Update by query
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY,
            [
                'index' => 'index',
                'query' => []
            ]
        ));
        
        // Reindex
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_REINDEX,
            [
                'index' => 'index',
                'destIndex' => 'phpunit'
            ]
        ));
    }
}
