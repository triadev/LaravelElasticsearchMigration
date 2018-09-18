<?php
namespace Tests\Integration;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Tests\TestCase;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;
use Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed;

class ElasticsearchMigrationTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $service;
    
    /** @var Client */
    private $esClient;
    
    /** @var \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract */
    private $migrationRepository;
    
    /** @var ElasticsearchMigrationDatabaseContract */
    private $elasticsearchMigrationDatabaseService;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->service = app(ElasticsearchMigrationContract::class);
        $this->elasticsearchMigrationDatabaseService = app(ElasticsearchMigrationDatabaseContract::class);
        $this->esClient = $this->buildElasticsearchClient();
        
        if ($this->esClient->indices()->exists(['index' => 'phpunit'])) {
            $this->esClient->indices()->delete([
                'index' => 'phpunit'
            ]);
        }
    
        if ($this->esClient->indices()->exists(['index' => 'phpunit_1.0.1'])) {
            $this->esClient->indices()->delete([
                'index' => 'phpunit_1.0.1'
            ]);
        }
        
        $this->migrationRepository = app(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract::class
        );
        
        $this->buildDatabaseTestData();
    }
    
    private function buildElasticsearchClient() : Client
    {
        $config = config('triadev-elasticsearch-migration');
        
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts([
            [
                'host' => $config['host'],
                'port' => $config['port'],
                'scheme' => $config['scheme'],
                'user' => $config['user'],
                'pass' => $config['pass']
            ]
        ]);
        
        return $clientBuilder->build();
    }
    
    private function buildDatabaseTestData()
    {
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('1.0.0'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            '1.0.0',
            'createIndex',
            'phpunit',
            [
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
        ));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            '1.0.0',
            'updateIndex',
            'phpunit',
            [
                'mappings' => [
                    'phpunit' => [
                        'properties' => [
                            'description' => [
                                'type' => 'text'
                            ]
                        ]
                    ]
                ],
                'settings' => [
                    'index' => [
                        'refresh_interval' => "60s"
                    ]
                ]
            ]
        ));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            '1.0.0',
            'updateIndex',
            'phpunit',
            [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'content' => [
                                'type' => 'custom',
                                'tokenizer' => 'whitespace'
                            ]
                        ]
                    ]
                ],
                'closeIndex' => true
            ]
        ));
    }
    
    /**
     * @test
     */
    public function it_creates_and_updates_mappings_and_settings()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
        
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
        
        $this->service->migrate('1.0.0');
    
        $this->assertTrue($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
        
        $mapping = $this->esClient->indices()->getMapping([
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]);
        
        $this->assertTrue(array_has($mapping, 'phpunit.mappings.phpunit.properties.title'));
        $this->assertTrue(array_has($mapping, 'phpunit.mappings.phpunit.properties.description'));
        
        $settings = $this->esClient->indices()->getSettings([
            'index' => 'phpunit'
        ]);
        
        $this->assertEquals('60s', array_get($settings, 'phpunit.settings.index.refresh_interval'));
        $this->assertEquals('custom', array_get($settings, 'phpunit.settings.index.analysis.analyzer.content.type'));
        $this->assertEquals('whitespace', array_get($settings, 'phpunit.settings.index.analysis.analyzer.content.tokenizer'));
        
        $migration = $this->migrationRepository->find('1.0.0');
    
        $this->assertEquals('1.0.0', $migration->migration);
        $this->assertEquals('done', $migration->status);
    }
    
    /**
     * @test
     */
    public function it_creates_and_updates_mappings_and_settings_with_database()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
        
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
        
        $this->service->migrate('1.0.0', 'database');
        
        $this->assertTrue($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
        
        $mapping = $this->esClient->indices()->getMapping([
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]);
        
        $this->assertTrue(array_has($mapping, 'phpunit.mappings.phpunit.properties.title'));
        $this->assertTrue(array_has($mapping, 'phpunit.mappings.phpunit.properties.description'));
        
        $settings = $this->esClient->indices()->getSettings([
            'index' => 'phpunit'
        ]);
        
        $this->assertEquals('60s', array_get($settings, 'phpunit.settings.index.refresh_interval'));
        $this->assertEquals('custom', array_get($settings, 'phpunit.settings.index.analysis.analyzer.content.type'));
        $this->assertEquals('whitespace', array_get($settings, 'phpunit.settings.index.analysis.analyzer.content.tokenizer'));
        
        $migration = $this->migrationRepository->find('1.0.0');
        
        $this->assertEquals('1.0.0', $migration->migration);
        $this->assertEquals('done', $migration->status);
    }
    
    /**
     * @test
     */
    public function it_deletes_an_index()
    {
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    
        $this->service->migrate('1.0.0');
    
        $this->assertTrue($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    
        $this->service->migrate('delete_index');
    
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     */
    public function it_deletes_an_index_with_database()
    {
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
        
        $this->service->migrate('1.0.0', 'database');
        
        $this->assertTrue($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('delete_index'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            'delete_index',
            'deleteIndex',
            'phpunit'
        ));
        
        $this->service->migrate('delete_index', 'database');
        
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     */
    public function it_adds_and_deletes_an_alias()
    {
        $this->service->migrate('1.0.0');
        
        $this->assertFalse($this->esClient->indices()->existsAlias([
            'name' => 'alias',
            'index' => 'phpunit'
        ]));
        
        $this->service->migrate('add_alias');
    
        $this->assertTrue($this->esClient->indices()->existsAlias([
            'name' => 'alias',
            'index' => 'phpunit'
        ]));
    
        $this->service->migrate('delete_alias');
    
        $this->assertFalse($this->esClient->indices()->existsAlias([
            'name' => 'alias',
            'index' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     */
    public function it_adds_and_deletes_an_alias_with_database()
    {
        $this->service->migrate('1.0.0', 'database');
        
        $this->assertFalse($this->esClient->indices()->existsAlias([
            'name' => 'alias',
            'index' => 'phpunit'
        ]));
    
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('add_alias'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            'add_alias',
            'alias',
            'phpunit',
            [
                'add' => [
                    'alias'
                ]
            ]
        ));
        
        $this->service->migrate('add_alias', 'database');
        
        $this->assertTrue($this->esClient->indices()->existsAlias([
            'name' => 'alias',
            'index' => 'phpunit'
        ]));
    
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('delete_alias'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            'delete_alias',
            'alias',
            'phpunit',
            [
                'remove' => [
                    'alias'
                ]
            ]
        ));
        
        $this->service->migrate('delete_alias', 'database');
        
        $this->assertFalse($this->esClient->indices()->existsAlias([
            'name' => 'alias',
            'index' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\IndexNotExist
     */
    public function it_throws_an_exception_if_reindex_dest_index_not_exist()
    {
        $this->service->migrate('1.0.0');
        $this->service->migrate('reindex');
    }
    
    /**
     * @test
     */
    public function it_reindex_an_index()
    {
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
    
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit_1.0.1',
            'type' => 'phpunit'
        ]));
        
        $this->service->migrate('1.0.0');
        
        $this->esClient->index([
            'index' => 'phpunit',
            'type' => 'phpunit',
            'id' => 'reindex_test',
            'body' => [
                'title' => 'Title'
            ]
        ]);
    
        $this->assertTrue($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
    
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit_1.0.1',
            'type' => 'phpunit'
        ]));
        
        $this->service->migrate('1.0.1');
        $this->service->migrate('reindex');
        
        $this->assertTrue($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit_1.0.1',
            'type' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     */
    public function it_reindex_an_index_with_database()
    {
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
        
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit_1.0.1',
            'type' => 'phpunit'
        ]));
        
        $this->service->migrate('1.0.0', 'database');
        
        $this->esClient->index([
            'index' => 'phpunit',
            'type' => 'phpunit',
            'id' => 'reindex_test',
            'body' => [
                'title' => 'Title'
            ]
        ]);
        
        $this->assertTrue($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
        
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit_1.0.1',
            'type' => 'phpunit'
        ]));
    
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('1.0.1'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            '1.0.1',
            'createIndex',
            'phpunit_1.0.1',
            [
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
        ));
        
        $this->service->migrate('1.0.1', 'database');
    
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('reindex'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            'reindex',
            'reindex',
            'phpunit',
            [
                'destIndex' => 'phpunit_1.0.1',
                'refreshSourceIndex' => true
            ]
        ));
        
        $this->service->migrate('reindex', 'database');
        
        $this->assertTrue($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit_1.0.1',
            'type' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     */
    public function it_deletes_documents_by_query()
    {
        $this->service->migrate('1.0.0');
    
        $this->esClient->index([
            'index' => 'phpunit',
            'type' => 'phpunit',
            'id' => 'reindex_test',
            'body' => [
                'title' => 'Title'
            ]
        ]);
    
        $this->esClient->indices()->refresh([
            'index' => 'phpunit'
        ]);
    
        $this->assertTrue($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
        
        $this->service->migrate('delete_by_query');
    
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     */
    public function it_deletes_documents_by_query_with_database()
    {
        $this->service->migrate('1.0.0', 'database');
        
        $this->esClient->index([
            'index' => 'phpunit',
            'type' => 'phpunit',
            'id' => 'reindex_test',
            'body' => [
                'title' => 'Title'
            ]
        ]);
        
        $this->esClient->indices()->refresh([
            'index' => 'phpunit'
        ]);
        
        $this->assertTrue($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
    
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('delete_by_query'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            'delete_by_query',
            'deleteByQuery',
            'phpunit',
            [
                'query' => [
                    'match' => [
                        'title' => 'Title'
                    ]
                ],
                'type' => 'phpunit',
                'options' => [
                    'conflicts' => 'proceed'
                ]
            ]
        ));
        
        $this->service->migrate('delete_by_query', 'database');
        
        $this->assertFalse($this->esClient->exists([
            'id' => 'reindex_test',
            'index' => 'phpunit',
            'type' => 'phpunit'
        ]));
    }
    
    /**
     * @test
     */
    public function it_updates_documents_by_query()
    {
        $this->service->migrate('1.0.0');
        
        $this->esClient->index([
            'index' => 'phpunit',
            'type' => 'phpunit',
            'id' => 'reindex_test',
            'body' => [
                'title' => 'Title',
                'count' => 1
            ]
        ]);
        
        $this->esClient->indices()->refresh([
            'index' => 'phpunit'
        ]);
        
        $this->assertEquals(
            1,
            $this->esClient->get([
                'id' => 'reindex_test',
                'index' => 'phpunit',
                'type' => 'phpunit'
            ])['_source']['count']
        );
        
        $this->service->migrate('update_by_query');
    
        $this->assertEquals(
            2,
            $this->esClient->get([
                'id' => 'reindex_test',
                'index' => 'phpunit',
                'type' => 'phpunit'
            ])['_source']['count']
        );
    }
    
    /**
     * @test
     */
    public function it_updates_documents_by_query_with_database()
    {
        $this->service->migrate('1.0.0', 'database');
        
        $this->esClient->index([
            'index' => 'phpunit',
            'type' => 'phpunit',
            'id' => 'reindex_test',
            'body' => [
                'title' => 'Title',
                'count' => 1
            ]
        ]);
        
        $this->esClient->indices()->refresh([
            'index' => 'phpunit'
        ]);
        
        $this->assertEquals(
            1,
            $this->esClient->get([
                'id' => 'reindex_test',
                'index' => 'phpunit',
                'type' => 'phpunit'
            ])['_source']['count']
        );
    
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->createMigration('update_by_query'));
        $this->assertTrue($this->elasticsearchMigrationDatabaseService->addMigration(
            'update_by_query',
            'updateByQuery',
            'phpunit',
            [
                'query' => [
                    'match' => [
                        'title' => 'Title'
                    ]
                ],
                'type' => 'phpunit',
                'script' => [
                    'source' => 'ctx._source.count++',
                    'lang' => 'painless'
                ],
                'options' => [
                    'conflicts' => 'proceed'
                ]
            ]
        ));
        
        $this->service->migrate('update_by_query', 'database');
        
        $this->assertEquals(
            2,
            $this->esClient->get([
                'id' => 'reindex_test',
                'index' => 'phpunit',
                'type' => 'phpunit'
            ])['_source']['count']
        );
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationAlreadyDone
     */
    public function it_throws_an_exception_if_migration_already_done()
    {
        $this->migrationRepository->createOrUpdate('1.0.0', 'done');
    
        $this->service->migrate('1.0.0');
    }
    
    /**
     * @test
     */
    public function it_updates_migration_status_from_error_to_done()
    {
        $this->migrationRepository->createOrUpdate('1.0.0', 'error');
        
        $this->assertEquals(
            'error',
            $this->migrationRepository->find('1.0.0')->getAttribute('status')
        );
        
        $this->service->migrate('1.0.0');
    
        $this->assertEquals(
            'done',
            $this->migrationRepository->find('1.0.0')->getAttribute('status')
        );
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed
     */
    public function it_throws_exception_if_field_migration_not_allowed()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
    
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    
        $this->service->migrate('1.0.0');
    
        $this->assertTrue($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    
        try {
            $this->service->migrate('field_datatype_migration_failed');
        } catch (FieldDatatypeMigrationFailed $e) {
            $this->assertCount(2, json_decode($e->getMessage()));
        
            throw $e;
        }
    }
}
