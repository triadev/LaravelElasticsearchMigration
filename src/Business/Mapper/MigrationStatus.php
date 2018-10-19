<?php
namespace Triadev\EsMigration\Business\Mapper;

class MigrationStatus
{
    const MIGRATION_STATUS_WAIT = 0;
    const MIGRATION_STATUS_RUNNING = 1;
    const MIGRATION_STATUS_DONE = 2;
    const MIGRATION_STATUS_ERROR = 3;
    
    /**
     * Is migration status valid
     *
     * @param int $status
     * @return bool
     */
    public function isMigrationStatusValid(int $status) : bool
    {
        $valid = [
            self::MIGRATION_STATUS_WAIT,
            self::MIGRATION_STATUS_RUNNING,
            self::MIGRATION_STATUS_DONE,
            self::MIGRATION_STATUS_ERROR
        ];
        
        if (in_array($status, $valid)) {
            return true;
        }
        
        return false;
    }
}
