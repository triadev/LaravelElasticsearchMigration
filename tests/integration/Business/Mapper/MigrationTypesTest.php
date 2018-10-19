<?php
namespace Tests\Integration\Business\Mapper;

use Tests\TestCase;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Business\Migration\CreateIndex;
use Triadev\EsMigration\Business\Migration\DeleteAlias;
use Triadev\EsMigration\Business\Migration\DeleteByQuery;
use Triadev\EsMigration\Business\Migration\DeleteIndex;
use Triadev\EsMigration\Business\Migration\PutAlias;
use Triadev\EsMigration\Business\Migration\Reindex;
use Triadev\EsMigration\Business\Migration\UpdateByQuery;
use Triadev\EsMigration\Business\Migration\UpdateIndexMapping;
use Triadev\EsMigration\Business\Migration\UpdateIndexSetting;

class MigrationTypesTest extends TestCase
{
    /**
     * @test
     */
    public function it_maps_type_to_migration_class()
    {
        $migrationTypeMapper = new MigrationTypes();
        
        $this->assertInstanceOf(
            CreateIndex::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_CREATE_INDEX)
        );
    
        $this->assertInstanceOf(
            UpdateIndexMapping::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_UPDATE_INDEX_MAPPING)
        );
    
        $this->assertInstanceOf(
            UpdateIndexSetting::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_UPDATE_INDEX_SETTING)
        );
    
        $this->assertInstanceOf(
            DeleteIndex::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_DELETE_INDEX)
        );
    
        $this->assertInstanceOf(
            PutAlias::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_PUT_ALIAS)
        );
    
        $this->assertInstanceOf(
            DeleteAlias::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_DELETE_ALIAS)
        );
    
        $this->assertInstanceOf(
            DeleteByQuery::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_DELETE_BY_QUERY)
        );
    
        $this->assertInstanceOf(
            UpdateByQuery::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_UPDATE_BY_QUERY)
        );
    
        $this->assertInstanceOf(
            Reindex::class,
            $migrationTypeMapper->mapTypeToClass($migrationTypeMapper::MIGRATION_TYPE_REINDEX)
        );
    }
}
