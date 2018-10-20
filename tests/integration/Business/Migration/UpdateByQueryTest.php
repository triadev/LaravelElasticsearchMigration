<?php
namespace Tests\Integration\Business\Migration;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\AbstractMigration;
use Triadev\EsMigration\Business\Migration\UpdateByQuery;
use Triadev\EsMigration\Models\MigrationStep;

class UpdateByQueryTest extends TestCase
{
    /** @var AbstractMigration */
    private $migration;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->migration = new UpdateByQuery();
    
        $client = $this->elasticsearchClients->get('phpunit')->indices();
        if ($client->exists(['index' => 'index'])) {
           $client->delete(['index' => 'index']);
        }
    
        $client->create([
            'index' => 'index',
            'body' => [
                'mappings' => [
                    'phpunit' => [
                        'dynamic' => 'strict',
                        'properties' => [
                            'title' => [
                                'type' => 'keyword'
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
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationStepValidation
     */
    public function it_throws_an_validation_exception()
    {
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY,
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
        $this->elasticsearchClients->get('phpunit')->delete(['index' => 'index']);
        
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY,
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
        $esClient->index([
            'index' => 'index',
            'type' => 'phpunit',
            'body' => [
                'title' => 'Title',
                'count' => 1
            ]
        ]);
    
        // Before update
        $esClient->indices()->refresh(['index' => 'index']);
        
        $result = $esClient->search([
            'body' => [
                'query' => [
                    'term' => [
                        'title' => 'Title'
                    ]
                ]
            ]
        ]);
        
        $this->assertEquals(1, array_get($result, 'hits.hits.0._source.count'));
    
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            $this->getValidPayload(),
            new \DateTime(),
            new \DateTime()
        ));
    
        // After update
        $esClient->indices()->refresh(['index' => 'index']);
        
        $result = $esClient->search([
            'body' => [
                'query' => [
                    'term' => [
                        'title' => 'Title'
                    ]
                ]
            ]
        ]);
    
        $this->assertEquals(2, array_get($result, 'hits.hits.0._source.count'));
    }
    
    private function getValidPayload() : array
    {
        return [
            'index' => 'index',
            'body' => [
                'query' => [
                    'term' => [
                        'title' => 'Title'
                    ]
                ],
                'script' => [
                    'source' => 'ctx._source.count++',
                    'lang' => 'painless'
                ]
            ]
        ];
    }
}
