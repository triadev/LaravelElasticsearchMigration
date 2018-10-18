<?php
namespace Tests\Integration\Business\Migration;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\AbstractMigration;
use Triadev\EsMigration\Business\Migration\CreateIndex;
use Triadev\EsMigration\Business\Repository\ElasticsearchClients;
use Triadev\EsMigration\Models\MigrationStep;

class CreateIndexTest extends TestCase
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
        
        $this->migration = new CreateIndex();
    
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
        if ($client->exists(['index' => 'index'])) {
            $client->delete(['index' => 'index']);
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
            MigrationTypes::MIGRATION_TYPE_CREATE_INDEX,
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
    public function it_fails_if_index_already_exist()
    {
        $this->elasticsearchClients->get('phpunit')->indices()->create(['index' => 'index']);
    
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_CREATE_INDEX,
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
        $this->assertFalse(
            $this->elasticsearchClients->get('phpunit')
                ->indices()
                ->exists(['index' => 'index'])
        );
        
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_CREATE_INDEX,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            $this->getValidPayload(),
            new \DateTime(),
            new \DateTime()
        ));
    
        $this->assertTrue(
            $this->elasticsearchClients->get('phpunit')
                ->indices()
                ->exists(['index' => 'index'])
        );
    }
    
    private function getValidPayload() : array
    {
        return [
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
        ];
    }
}
