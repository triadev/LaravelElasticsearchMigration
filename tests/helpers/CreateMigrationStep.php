<?php
namespace Tests\Helpers;

use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;

class CreateMigrationStep extends \PHPUnit\Framework\TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $migrationService;
    
    /**
     * CreateMigrationStep constructor.
     * @param null|string $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    
        $this->migrationService = app(ElasticsearchMigrationContract::class);
    }
    
    public function createIndex(int $priority = 1)
    {
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_CREATE_INDEX,
            [
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
            ],
            $priority
        ));
    }
    
    public function updateIndex(int $priority = 1, bool $stopOnFailure = true)
    {
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            [
                'index' => 'index'
            ],
            $priority,
            $stopOnFailure
        ));
    }
    
    public function deleteIndex(int $priority = 1)
    {
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_INDEX,
            [
                'index' => 'index'
            ],
            $priority
        ));
    }
    
    public function alias(int $priority = 1)
    {
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_PUT_ALIAS,
            [
                'index' => 'index'
            ],
            $priority
        ));
    }
    
    public function deleteByQuery(int $priority = 1)
    {
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY,
            [
                'index' => 'index',
                'query' => []
            ],
            $priority
        ));
    }
    
    public function updateByQuery(int $priority = 1)
    {
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY,
            [
                'index' => 'index',
                'query' => []
            ],
            $priority
        ));
    }
    
    public function reindex(int $priority = 1)
    {
        $this->assertTrue($this->migrationService->addMigrationStep(
            'phpunit',
            MigrationTypes::MIGRATION_TYPE_REINDEX,
            [
                'index' => 'index',
                'destindex' => 'phpunit'
            ],
            $priority
        ));
    }
}
