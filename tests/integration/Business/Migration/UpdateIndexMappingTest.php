<?php
namespace Tests\Integration\Business\Migration;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\AbstractMigration;
use Triadev\EsMigration\Business\Migration\UpdateIndexMapping;
use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
use Triadev\EsMigration\Models\MigrationStep;

class UpdateIndexMappingTest extends TestCase
{
    /** @var ElasticsearchClients */
    private $elasticsearchClients;
    
    /** @var AbstractMigration */
    private $migration;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->migration = new UpdateIndexMapping();
    
        $this->elasticsearchClients = new ElasticsearchClients();
        $this->elasticsearchClients->add(
            'phpunit',
            'localhost',
            env('ELASTICSEARCH_PORT'),
            'http',
            '',
            ''
        );
        
        $client = $this->elasticsearchClients->get('phpunit')->indices();
        if (!$client->exists(['index' => 'index'])) {
            $client->create([
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
            ]);
        }
    }
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationStepValidation
     */
    public function it_throws_an_validation_exception()
    {
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [],
            new \DateTime(),
            new \DateTime()
        ));
    }
    
    /**
     * @test
     * @expectedException \Exception
     */
    public function it_fails_if_index_not_exist()
    {
        $this->elasticsearchClients->get('phpunit')->indices()->delete(['index' => 'index']);
        
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            $this->getValidPayload(),
            new \DateTime(),
            new \DateTime()
        ));
    }
    
    /**
     * @test
     */
    public function it_runs_migration()
    {
        $esClient = $this->elasticsearchClients->get('phpunit');
        
        $mapping = $esClient->indices()->getMapping(['index' => 'index']);
        $this->assertNull(array_get($mapping, 'index.mappings.phpunit.properties.update'));
        
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            $this->getValidPayload(),
            new \DateTime(),
            new \DateTime()
        ));
    
        $mapping = $esClient->indices()->getMapping(['index' => 'index']);
        $this->assertEquals([
            'type' => 'text'
        ], array_get($mapping, 'index.mappings.phpunit.properties.update'));
    }
    
    private function getValidPayload() : array
    {
        return [
            'index' => 'index',
            'type' => 'phpunit',
            'body' => [
                'properties' => [
                    'update' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];
    }
}
