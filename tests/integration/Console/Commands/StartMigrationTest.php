<?php
namespace Tests\Integration\Console\Commands;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Tests\TestCase;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;

class StartMigrationTest extends TestCase
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
        
        if ($this->esClient->indices()->exists(['index' => 'phpunit'])) {
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
    public function it_creates_and_updates_mappings_and_settings_with_command()
    {
        $this->assertNull($this->migrationRepository->find('1.0.0'));
        
        $this->assertFalse($this->esClient->indices()->exists([
            'index' => 'phpunit'
        ]));
        
        $this->artisan('triadev:elasticsearch:migration:start', ['version' => '1.0.0']);
    
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
        $this->assertEquals('done', $migration->status);
    }
}
