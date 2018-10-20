<?php
namespace Tests\Integration\Business\Migration;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\AbstractMigration;
use Triadev\EsMigration\Business\Migration\DeleteByQuery;
use Triadev\EsMigration\Models\MigrationStep;

class DeleteByQueryTest extends TestCase
{
    /** @var AbstractMigration */
    private $migration;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->migration = new DeleteByQuery();
    
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
            MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY,
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
            MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY,
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
                'title' => 'Title'
            ]
        ]);
    
        $esClient->indices()->refresh(['index' => 'index']);
        $this->assertEquals(1, $esClient->count(['index' => 'index'])['count']);
    
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            $this->getValidPayload(),
            new \DateTime(),
            new \DateTime()
        ));
    
        $esClient->indices()->refresh(['index' => 'index']);
        $this->assertEquals(0, $esClient->count(['index' => 'index'])['count']);
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
                ]
            ]
        ];
    }
}
