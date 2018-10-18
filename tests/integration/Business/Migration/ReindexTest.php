<?php
namespace Tests\Integration\Business\Migration;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\AbstractMigration;
use Triadev\EsMigration\Business\Migration\PutAlias;
use Triadev\EsMigration\Business\Migration\Reindex;
use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
use Triadev\EsMigration\Models\MigrationStep;

class ReindexTest extends TestCase
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
        
        $this->migration = new Reindex();
    
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
        if ($client->exists(['index' => 'source_index,dest_index'])) {
            $client->delete(['index' => 'source_index,dest_index']);
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
            MigrationTypes::MIGRATION_TYPE_REINDEX,
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
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_REINDEX,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [
                'body' => [
                    'source' => [
                        'index' => 'source_index'
                    ],
                    'dest' => [
                        'index' => 'dest_index'
                    ]
                ]
            ],
            new \DateTime(),
            new \DateTime()
        ));
    }
    
    /**
     * @test
     */
    public function it_runs_migration()
    {
        $this->createIndices();
    
        $esClient = $this->elasticsearchClients->get('phpunit');
        $esClient->index([
            'index' => 'source_index',
            'type' => 'phpunit',
            'body' => [
                'title' => 'Title'
            ]
        ]);
        
        $esClient->indices()->refresh(['index' => 'source_index,dest_index']);
        $this->assertEquals(1, $esClient->count(['index' => 'source_index'])['count']);
        $this->assertEquals(0, $esClient->count(['index' => 'dest_index'])['count']);
    
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_REINDEX,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [
                'body' => [
                    'source' => [
                        'index' => 'source_index'
                    ],
                    'dest' => [
                        'index' => 'dest_index'
                    ]
                ]
            ],
            new \DateTime(),
            new \DateTime()
        ));
    
        $esClient->indices()->refresh(['index' => 'source_index,dest_index']);
        $this->assertEquals(1, $esClient->count(['index' => 'source_index'])['count']);
        $this->assertEquals(1, $esClient->count(['index' => 'dest_index'])['count']);
    }
    
    private function createIndices()
    {
        $esClient = $this->elasticsearchClients->get('phpunit');
    
        $esClient->indices()->create([
            'index' => 'source_index',
            'body' => [
                'mappings' => [
                    'phpunit' => [
                        'dynamic' => 'strict',
                        'properties' => [
                            'title' => [
                                'type' => 'text'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    
        $esClient->indices()->create([
            'index' => 'dest_index',
            'body' => [
                'mappings' => [
                    'phpunit' => [
                        'dynamic' => 'strict',
                        'properties' => [
                            'title' => [
                                'type' => 'text'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
