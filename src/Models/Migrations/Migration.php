<?php
namespace Triadev\EsMigration\Models\Migrations;

class Migration
{
    /** @var string */
    private $index;
    
    /**
     * Migration constructor.
     * @param string $index
     */
    public function __construct(string $index)
    {
        $this->index = $index;
    }
    
    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }
}
