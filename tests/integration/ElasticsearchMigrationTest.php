<?php
namespace Tests\Integration;

use Elasticsearch\Client;
use Tests\TestCase;
use Triadev\EsMigration\Business\Events\MigrationDone;
use Triadev\EsMigration\Business\Events\MigrationRunning;
use Triadev\EsMigration\Business\Events\MigrationStepDone;
use Triadev\EsMigration\Business\Events\MigrationStepRunning;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract as ElasticsearchMigrationRepository;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract as ElasticsearchMigrationStepRepository;
use Triadev\EsMigration\Models\Entity\ElasticsearchMigration;

class ElasticsearchMigrationTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $migrationService;
    
    /** @var ElasticsearchMigrationRepository */
    private $migrationRepository;
    
    /** @var ElasticsearchMigrationStepRepository */
    private $migrationStepRepository;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->migrationService = app(ElasticsearchMigrationContract::class);
        $this->migrationRepository = app(ElasticsearchMigrationRepository::class);
        $this->migrationStepRepository = app(ElasticsearchMigrationStepRepository::class);
    
        /** @var Client $esClient */
        $esClient = $this->elasticsearchClients->get('phpunit');
        if ($esClient->indices()->exists(['index' => 'index'])) {
            $esClient->indices()->delete(['index' => 'index']);
        }
    }
    
    /**
     * @test
     */
    public function it_creates_a_migration()
    {
        $this->assertNull($this->migrationRepository->find('phpunit'));
        
        $this->assertTrue(
            $this->migrationService->createMigration(
                'phpunit'
            )
        );
        
        $this->assertInstanceOf(
            ElasticsearchMigration::class,
            $this->migrationRepository->find('phpunit')
        );
    }
    
    /**
     * @test
     */
    public function it_adds_migration_steps_with_priority()
    {
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
        
        $this->assertEquals(
            0,
            $this->migrationRepository->find('phpunit')->migrationSteps()->count()
        );
        
        $this->addMigrationSteps();
        
        $migrationSteps = $this->migrationRepository->find('phpunit')->migrationSteps()->getResults();
        
        $this->assertEquals(7, count($migrationSteps));
        
        $this->assertEquals(MigrationTypes::MIGRATION_TYPE_CREATE_INDEX, $migrationSteps[0]->type);
        $this->assertEquals(MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING, $migrationSteps[1]->type);
        $this->assertEquals(MigrationTypes::MIGRATION_TYPE_DELETE_INDEX, $migrationSteps[2]->type);
        $this->assertEquals(MigrationTypes::MIGRATION_TYPE_PUT_ALIAS, $migrationSteps[3]->type);
        $this->assertEquals(MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY, $migrationSteps[4]->type);
        $this->assertEquals(MigrationTypes::MIGRATION_TYPE_REINDEX, $migrationSteps[5]->type);
        $this->assertEquals(MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY, $migrationSteps[6]->type);
    }
    
    /**
     * @test
     */
    public function it_returns_false_if_migration_step_type_is_invalid()
    {
        $this->assertFalse($this->migrationService->addMigrationStep(
            'phpunit',
            'invalid',
            [
                'index' => 'index'
            ]
        ));
    }
    
    /**
     * @test
     */
    public function it_returns_false_if_migration_not_exist()
    {
        $this->assertFalse($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            [
                'index' => 'index'
            ]
        ));
    }
    
    /**
     * @test
     */
    public function it_gets_migration_status()
    {
        $this->assertEquals([
            'migration' => 'phpunit',
            'status' => null,
            'steps' => []
        ], $this->migrationService->getMigrationStatus('phpunit'));
    
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
        
        $this->addMigrationSteps();
        
        $result = $this->migrationService->getMigrationStatus('phpunit');
        
        $this->assertEquals('phpunit', $result['migration']);
        $this->assertEquals( MigrationStatus::MIGRATION_STATUS_WAIT, $result['status']);
        
        $this->assertCount(7, $result['steps']);
        
        foreach ($result['steps'] as $step) {
            $this->assertEquals(MigrationStatus::MIGRATION_STATUS_WAIT, $step['status']);
            $this->assertEquals(null, $step['error']);
        }
    }
    
    /**
     * @test
     */
    public function it_starts_migration()
    {
        $this->expectsEvents([
            MigrationRunning::class,
            MigrationDone::class,
            MigrationStepRunning::class,
            MigrationStepDone::class
        ]);
        
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
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
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_INDEX,
            [
                'index' => 'index'
            ]
        ));
        
        $result = $this->migrationService->getMigrationStatus('phpunit');
        $this->assertEquals('phpunit', $result['migration']);
        $this->assertEquals( MigrationStatus::MIGRATION_STATUS_WAIT, $result['status']);
        
        $this->migrationService->startMigration('phpunit', $this->elasticsearchClients);
    
        $result = $this->migrationService->getMigrationStatus('phpunit');
        
        $this->assertEquals('phpunit', $result['migration']);
        $this->assertEquals( MigrationStatus::MIGRATION_STATUS_DONE, $result['status']);
        
        foreach ($result['steps'] as $step) {
            $this->assertEquals(MigrationStatus::MIGRATION_STATUS_DONE, $step['status']);
            $this->assertEquals(null, $step['error']);
        }
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationAlreadyDone
     */
    public function it_throws_an_exception_if_a_migration_already_done()
    {
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
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
        
        $this->migrationService->startMigration('phpunit', $this->elasticsearchClients);
        $this->migrationService->startMigration('phpunit', $this->elasticsearchClients);
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
            ],
            1
        ));
    
        // Update index
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            [
                'index' => 'index'
            ],
            2
        ));
    
        // Delete index
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_INDEX,
            [
                'index' => 'index'
            ],
            2
        ));
    
        // Alias
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_PUT_ALIAS,
            [
                'index' => 'index'
            ],
            2
        ));
    
        // Delete by query
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY,
            [
                'index' => 'index',
                'query' => []
            ],
            2
        ));
    
        // Update by query
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY,
            [
                'index' => 'index',
                'query' => []
            ],
            3
        ));
    
        // Reindex
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_REINDEX,
            [
                'index' => 'index',
                'destIndex' => 'phpunit'
            ],
            2
        ));
    }
}
