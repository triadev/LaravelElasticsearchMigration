<?php
namespace Tests\Integration\Business\Migration;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationStatus;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\AbstractMigration;
use Triadev\EsMigration\Business\Migration\UpdateIndexSetting;
use Triadev\EsMigration\Models\MigrationStep;

class UpdateIndexSettingTest extends TestCase
{
    /** @var AbstractMigration */
    private $migration;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        parent::setUp();
        
        $this->migration = new UpdateIndexSetting();
        
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
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_SETTING,
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
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_SETTING,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            $this->getValidPayload(),
            1,
            true,
            new \DateTime(),
            new \DateTime()
        ));
    }
    
    /**
     * @test
     * @expectedException \Elasticsearch\Common\Exceptions\BadRequest400Exception
     */
    public function it_fails_if_non_dynamic_settings_insert_without_close_index()
    {
        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_SETTING,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            [
                'index' => 'index',
                'body' => [
                    'refresh_interval' => '1s',
                    'analysis' => [
                        'analyzer' => [
                            'content' => [
                                'type' => 'custom',
                                'tokenizer' => 'whitespace'
                            ]
                        ]
                    ]
                ]
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
        
        $setting = $esClient->indices()->getSettings(['index' => 'index']);
        $this->assertEquals('30s', array_get($setting, 'index.settings.index.refresh_interval'));

        $this->migration->migrate($this->elasticsearchClients->get('phpunit'), new MigrationStep(
            1,
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_SETTING,
            MigrationStatus::MIGRATION_STATUS_WAIT,
            null,
            $this->getValidPayload(),
            1,
            true,
            new \DateTime(),
            new \DateTime()
        ));
    
        $setting = $esClient->indices()->getSettings(['index' => 'index']);
        $this->assertEquals('1s', array_get($setting, 'index.settings.index.refresh_interval'));
    }
    
    private function getValidPayload() : array
    {
        return [
            'index' => 'index',
            'body' => [
                'refresh_interval' => '1s'
            ]
        ];
    }
}
