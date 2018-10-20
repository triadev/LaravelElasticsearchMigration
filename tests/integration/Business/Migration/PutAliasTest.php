<?php
namespace Tests\Integration\Business\Migration;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\AbstractMigration;
use Triadev\EsMigration\Business\Migration\PutAlias;
use Triadev\EsMigration\Models\MigrationStep;

class PutAliasTest extends TestCase
{
    /** @var AbstractMigration */
    private $migration;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->migration = new PutAlias();
        
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
    
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\MigrationStepValidation
     */
    public function it_throws_an_validation_exception()
    {
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_PUT_ALIAS,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [],
            1,
            true,
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
            MigrationTypes::MIGRATION_TYPE_PUT_ALIAS,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [
                'index' => 'index',
                'name' => 'Alias'
            ],
            1,
            true,
            new \DateTime(),
            new \DateTime()
        ));
    }
    
    /**
     * @test
     * @expectedException \Exception
     */
    public function it_fails_if_alias_already_exist()
    {
        $this->elasticsearchClients->get('phpunit')->indices()->putAlias([
            'index' => 'index',
            'name' => 'Alias'
        ]);
        
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_PUT_ALIAS,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [
                'index' => 'index',
                'name' => 'Alias'
            ],
            1,
            true,
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
        
        $this->assertFalse($esClient->indices()->existsAlias([
            'index' => 'index',
            'name' => 'Alias'
        ]));
    
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_PUT_ALIAS,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [
                'index' => 'index',
                'name' => 'Alias'
            ],
            1,
            true,
            new \DateTime(),
            new \DateTime()
        ));
    
        $this->assertTrue($esClient->indices()->existsAlias([
            'index' => 'index',
            'name' => 'Alias'
        ]));
    }
}
