<?php
namespace Tests;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;

class ElasticsearchMigrationTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $service;
    
    /** @var Client */
    private $esClient;
    
    /** @var \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract */
    private $migrationRepository;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->service = app(ElasticsearchMigrationContract::class);
        $this->esClient = $this->buildElasticsearchClient();
        
        if ($this->esClient->indices()->exists(['index' => 'phpunit']))
        {
            $this->esClient->indices()->delete([
                'index' => 'phpunit'
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
        
        $this->assertTrue(array_has($mapping, 'phpunit.mappings.phpunit.properties.title1'));
        $this->assertTrue(array_has($mapping, 'phpunit.mappings.phpunit.properties.title2'));
        
        $this->assertEquals('60s', array_get(
            $this->esClient->indices()->getSettings([
                'index' => 'phpunit'
            ]),
            'phpunit.settings.index.refresh_interval'
        ));
        
        $migration = $this->migrationRepository->find('1.0.0');
    
        $this->assertEquals('1.0.0', $migration->migration);
        $this->assertEquals('done', $migration->status);
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
}
