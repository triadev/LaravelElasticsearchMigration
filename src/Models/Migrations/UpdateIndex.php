<?php
namespace Triadev\EsMigration\Models\Migrations;

class UpdateIndex extends Migration
{
    /** @var array|null */
    private $mappings;
    
    /** @var array|null */
    private $settings;
    
    /** @var bool */
    private $closeIndex = false;
    
    /**
     * UpdateIndex constructor.
     * @param string $index
     */
    public function __construct(string $index)
    {
        parent::__construct($index);
    }
    
    /**
     * @return array|null
     */
    public function getMappings(): ?array
    {
        return $this->mappings;
    }
    
    /**
     * @param array|null $mappings
     * @return UpdateIndex
     */
    public function setMappings(?array $mappings): UpdateIndex
    {
        $this->mappings = $mappings;
        
        return $this;
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
     * @return UpdateIndex
     */
    public function setSettings(?array $settings): UpdateIndex
    {
        $this->settings = $settings;
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isCloseIndex(): bool
    {
        return $this->closeIndex;
    }
    
    /**
     * @param bool $closeIndex
     * @return UpdateIndex
     */
    public function setCloseIndex(bool $closeIndex): UpdateIndex
    {
        $this->closeIndex = $closeIndex;
        
        return $this;
    }
}
