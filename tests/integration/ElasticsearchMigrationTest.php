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
use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
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
    public function it_deletes_a_migration_with_all_steps()
    {
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
        
        $this->addMigrationSteps();
        
        $this->assertNotNull($this->migrationRepository->find('phpunit'));
        $this->assertNotNull($this->migrationStepRepository->find(1));
        $this->assertEquals(7, $this->migrationRepository->find('phpunit')->migrationSteps()->count());
        
        $this->assertTrue($this->migrationService->deleteMigration('phpunit'));
        
        $this->assertNull($this->migrationRepository->find('phpunit'));
        $this->assertNull($this->migrationStepRepository->find(1));
    }
    
    /**
     * @test
     */
    public function it_deletes_a_migration_step()
    {
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
        
        $this->addMigrationSteps();
        
        $this->assertEquals(
            7,
            $this->migrationRepository->find('phpunit')->migrationSteps()->count()
        );
        
        $this->migrationService->deleteMigrationStep(1);
        $this->migrationService->deleteMigrationStep(2);
        $this->migrationService->deleteMigrationStep(3);
        
        $this->assertEquals(
            4,
            $this->migrationRepository->find('phpunit')->migrationSteps()->count()
        );
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
            'error' => null,
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
    
            $this->assertTrue(is_int($step['id']));
            $this->assertTrue(is_array($step['params']));
            $this->assertTrue(is_int($step['priority']));
            $this->assertTrue(is_bool($step['stop_on_failure']));
            $this->assertTrue($step['created_at'] instanceof \DateTime);
            $this->assertTrue($step['updated_at'] instanceof \DateTime);
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
     */
    public function it_restarts_migration_also_if_migration_status_is_done()
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
        
        // set status on done
        $this->migrationRepository->createOrUpdate('phpunit', MigrationStatus::MIGRATION_STATUS_DONE);
        
        // check if status is done
        $result = $this->migrationService->getMigrationStatus('phpunit');
        $this->assertEquals( MigrationStatus::MIGRATION_STATUS_DONE, $result['status']);
        
        $this->migrationService->restartMigration('phpunit', $this->elasticsearchClients);
        
        $result = $this->migrationService->getMigrationStatus('phpunit');
        $this->assertEquals( MigrationStatus::MIGRATION_STATUS_DONE, $result['status']);
        
        foreach ($result['steps'] as $step) {
            $this->assertEquals(MigrationStatus::MIGRATION_STATUS_DONE, $step['status']);
            $this->assertEquals(null, $step['error']);
        }
    }
    
    /**
     * @test
     */
    public function the_migration_fails_if_no_alive_elasticsearch_nodes_found_in_cluster()
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
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_INDEX,
            [
                'index' => 'index'
            ]
        ));
    
        $clients = new ElasticsearchClients();
        $clients->add(
            'phpunit',
            'INVALID',
            env('ELASTICSEARCH_PORT'),
            'http',
            '',
            ''
        );
    
        $this->migrationService->startMigration('phpunit', $clients);
    
        $result = $this->migrationService->getMigrationStatus('phpunit');
        
        $this->assertEquals( MigrationStatus::MIGRATION_STATUS_ERROR, array_get($result, 'status'));
        $this->assertEquals( 'No alive nodes found in your cluster', array_get($result, 'error'));
    }
    
    /**
     * @test
     */
    public function it_starts_migration_and_stop_pipeline_on_failure()
    {
        $this->runStopOnFailurePipeline(true);
    
        $this->migrationService->startMigration('phpunit', $this->elasticsearchClients);
        $this->assertFalse($this->elasticsearchClients->get('phpunit')->indices()->exists(['index' => 'index']));
    }
    
    /**
     * @test
     */
    public function it_starts_migration_and_continue_pipeline_on_failure()
    {
        $this->runStopOnFailurePipeline(false);
        
        $this->migrationService->startMigration('phpunit', $this->elasticsearchClients);
        $this->assertTrue($this->elasticsearchClients->get('phpunit')->indices()->exists(['index' => 'index']));
    }
    
    private function runStopOnFailurePipeline(bool $stopOnFailure)
    {
        $this->assertTrue($this->migrationService->createMigration('phpunit'));
        
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            [
                'index' => 'index'
            ],
            1,
            $stopOnFailure
        ));
    
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
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationAlreadyDone
     */
    public function it_throws_an_exception_if_a_migration_already_done_when_start_pipeline()
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
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationAlreadyRunning
     */
    public function it_throws_an_exception_if_a_migration_already_running_when_start_pipeline()
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
        
        $this->migrationRepository->createOrUpdate(
            'phpunit',
            MigrationStatus::MIGRATION_STATUS_RUNNING,
            null
        );
        
        $this->migrationService->startMigration('phpunit', $this->elasticsearchClients);
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationAlreadyRunning
     */
    public function it_throws_an_exception_if_a_migration_already_running_when_restart_pipeline()
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
        
        $this->migrationRepository->createOrUpdate(
            'phpunit',
            MigrationStatus::MIGRATION_STATUS_RUNNING,
            null
        );
        
        $this->migrationService->restartMigration('phpunit', $this->elasticsearchClients);
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
