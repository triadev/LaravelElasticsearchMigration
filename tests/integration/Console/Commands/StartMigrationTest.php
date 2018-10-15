<?php
namespace Tests\Integration\Console\Commands;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Tests\TestCase;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;

class StartMigrationTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $service;
    
    /** @var Client */
    private $esClient;
    
    /** @var \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract */
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
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::class
        );
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
    
    /**
     * @test
     */
    public function it_creates_and_updates_mappings_and_settings_with_command()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
        
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
        
        $this->artisan('triadev:elasticsearch:migration:start', [
            'versions' => '1.0.0'
        ])->assertExitCode(0);
    
        $this->assertTrue($this->esClient->indices()->exists(['index' => 'phpunit']));
    
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
        $this->assertEquals(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE,
            $migration->status
        );
    }
    
    /**
     * @test
     */
    public function it_creates_and_updates_mappings_and_settings_with_command_with_database()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
        
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
    
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
        
        $this->artisan('triadev:elasticsearch:migration:start', [
            'versions' => '1.0.0',
            '--source' => 'database'
        ])->assertExitCode(0);
    
        $this->assertTrue($this->esClient->indices()->exists(['index' => 'phpunit']));
    
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
        $this->assertEquals(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE,
            $migration->status
        );
    }
    
    /**
     * @test
     */
    public function it_starts_to_orchestra_migrations()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
        $this->assertNull($this->migrationRepository->find('1.0.1'));
    
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit,phpunit_1.0.1'
        ]));
    
        $this->artisan('triadev:elasticsearch:migration:start', [
            'versions' => '1.0.0, 1.0.1'
        ])->assertExitCode(0);
    
        $this->assertTrue($this->esClient->indices()->exists(['index' => 'phpunit,phpunit_1.0.1']));
        
        $migrationResult = $this->migrationRepository->find('1.0.0');
        $this->assertEquals(\Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE, $migrationResult->status);
        
        $migrationResult = $this->migrationRepository->find('1.0.1');
        $this->assertEquals(\Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE, $migrationResult->status);
    }
    
    /**
     * @test
     */
    public function it_starts_to_orchestra_migrations_with_database()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
        $this->assertNull($this->migrationRepository->find('1.0.1'));
    
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit,phpunit_1.0.1'
        ]));
    
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
                ]
            ]
        ));
    
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
                ]
            ]
        ));
    
        $this->artisan('triadev:elasticsearch:migration:start', [
            'versions' => '1.0.0, 1.0.1',
            '--source' => 'database'
        ])->assertExitCode(0);
    
        $this->assertTrue($this->esClient->indices()->exists(['index' => 'phpunit,phpunit_1.0.1']));
        
        $this->assertEquals(\Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE, $this->migrationRepository->find('1.0.0')->status);
        $this->assertEquals(\Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::ELASTICSEARCH_MIGRATION_STATUS_DONE, $this->migrationRepository->find('1.0.1')->status);
    }
}
