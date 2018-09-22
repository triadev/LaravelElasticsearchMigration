<?php
namespace Triadev\EsMigration\Models\Migrations;

class DeleteIndex extends Migration
{
    /**
     * DeleteIndex constructor.
     * @param string $index
     */
    public function __construct(string $index)
    {
        parent::__construct($index);
    }
}
