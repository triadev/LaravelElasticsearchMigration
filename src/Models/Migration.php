<?php
namespace Triadev\EsMigration\Models;

class Migration
{
    /** @var string */
    private $index;
    
    /** @var string */
    private $type;
    
    /** @var array|null */
    private $mappings;
    
    /** @var array|null */
    private $settings;
    
    /**
     * Migrations constructor.
     * @param string $index
     * @param string $type
     */
    public function __construct(string $index, string $type)
    {
        $this->index = $index;
        $this->type = $type;
    }
    
    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }
    
    /**
     * @param string $index
     */
    public function setIndex(string $index)
    {
        $this->index = $index;
    }
    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
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
     */
    public function setMappings(?array $mappings)
    {
        $this->mappings= $mappings;
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
     */
    public function setSettings(?array $settings): void
    {
        $this->settings = $settings;
    }
}
