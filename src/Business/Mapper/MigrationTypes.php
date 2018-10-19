<?php
namespace Triadev\EsMigration\Business\Mapper;

use Triadev\EsMigration\Business\Migration\AbstractMigration;

use Triadev\EsMigration\Business\Migration\CreateIndex;
use Triadev\EsMigration\Business\Migration\DeleteAlias;
use Triadev\EsMigration\Business\Migration\DeleteByQuery;
use Triadev\EsMigration\Business\Migration\DeleteIndex;
use Triadev\EsMigration\Business\Migration\PutAlias;
use Triadev\EsMigration\Business\Migration\Reindex;
use Triadev\EsMigration\Business\Migration\UpdateByQuery;
use Triadev\EsMigration\Business\Migration\UpdateIndexMapping;
use Triadev\EsMigration\Business\Migration\UpdateIndexSetting;

class MigrationTypes
{
    const MIGRATION_TYPE_CREATE_INDEX = 'createIndex';
    const MIGRATION_TYPE_UPDATE_INDEX_MAPPING = 'updateIndexMapping';
    const MIGRATION_TYPE_UPDATE_INDEX_SETTING = 'updateIndexMappingSetting';
    const MIGRATION_TYPE_DELETE_INDEX = 'deleteIndex';
    const MIGRATION_TYPE_PUT_ALIAS = 'putAlias';
    const MIGRATION_TYPE_DELETE_ALIAS = 'deleteAlias';
    const MIGRATION_TYPE_DELETE_BY_QUERY = 'deleteByQuery';
    const MIGRATION_TYPE_UPDATE_BY_QUERY = 'updateByQuery';
    const MIGRATION_TYPE_REINDEX = 'reindex';
    
    /**
     * Is migration type valid
     *
     * @param string $type
     * @return bool
     */
    public function isMigrationTypeValid(string $type) : bool
    {
        if (in_array($type, [
            self::MIGRATION_TYPE_CREATE_INDEX,
            self::MIGRATION_TYPE_UPDATE_INDEX_MAPPING,
            self::MIGRATION_TYPE_UPDATE_INDEX_SETTING,
            self::MIGRATION_TYPE_DELETE_INDEX,
            self::MIGRATION_TYPE_PUT_ALIAS,
            self::MIGRATION_TYPE_DELETE_ALIAS,
            self::MIGRATION_TYPE_DELETE_BY_QUERY,
            self::MIGRATION_TYPE_UPDATE_BY_QUERY,
            self::MIGRATION_TYPE_REINDEX,
        ])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Map type to class
     *
     * @param string $type
     * @return null|AbstractMigration
     */
    public function mapTypeToClass(string $type) : ?AbstractMigration
    {
        switch ($type) {
            case MigrationTypes::MIGRATION_TYPE_CREATE_INDEX:
                $migrationClass = new CreateIndex();
                break;
            case MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_MAPPING:
                $migrationClass = new UpdateIndexMapping();
                break;
            case MigrationTypes::MIGRATION_TYPE_UPDATE_INDEX_SETTING:
                $migrationClass = new UpdateIndexSetting();
                break;
            case MigrationTypes::MIGRATION_TYPE_DELETE_INDEX:
                $migrationClass = new DeleteIndex();
                break;
            case MigrationTypes::MIGRATION_TYPE_PUT_ALIAS:
                $migrationClass = new PutAlias();
                break;
            case MigrationTypes::MIGRATION_TYPE_DELETE_ALIAS:
                $migrationClass = new DeleteAlias();
                break;
            case MigrationTypes::MIGRATION_TYPE_DELETE_BY_QUERY:
                $migrationClass = new DeleteByQuery();
                break;
            case MigrationTypes::MIGRATION_TYPE_UPDATE_BY_QUERY:
                $migrationClass = new UpdateByQuery();
                break;
            case MigrationTypes::MIGRATION_TYPE_REINDEX:
                $migrationClass = new Reindex();
                break;
            default:
                $migrationClass = null;
                break;
        }
        
        return $migrationClass;
    }
}
