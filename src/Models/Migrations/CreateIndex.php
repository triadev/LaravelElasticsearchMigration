<?php
namespace Triadev\EsMigration\Models\Migrations;

class CreateIndex extends Migration
{
    /** @var array */
    private $mappings;
    
    /** @var array|null */
    private $settings;
    
    /**
     * CreateIndex constructor.
     * @param string $index
     * @param array $mappings
     */
    public function __construct(string $index, array $mappings)
    {
        parent::__construct($index);
        
        $this->mappings = $mappings;
    }
    
    /**
     * @return array
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }
    
    /**
     * @return array|null
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }
    
    /**
     * @param array|null $settings
     * @return CreateIndex
     */
    public function setSettings(?array $settings): CreateIndex
    {
        $this->settings = $settings;
        
        return $this;
    }
}
