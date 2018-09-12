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
    
    /** @var bool */
    private $closeIndex = false;
    
    /** @var Alias|null */
    private $alias;
    
    /** @var Reindex|null */
    private $reindex;
    
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
    
    /**
     * @return bool
     */
    public function isCloseIndex(): bool
    {
        return $this->closeIndex;
    }
    
    /**
     * @param bool $closeIndex
     */
    public function setCloseIndex(bool $closeIndex): void
    {
        $this->closeIndex = $closeIndex;
    }
    
    /**
     * @return null|Alias
     */
    public function getAlias(): ?Alias
    {
        return $this->alias;
    }
    
    /**
     * @param null|Alias $alias
     */
    public function setAlias(?Alias $alias): void
    {
        $this->alias = $alias;
    }
    
    /**
     * @return null|Reindex
     */
    public function getReindex(): ?Reindex
    {
        return $this->reindex;
    }
    
    /**
     * @param null|Reindex $reindex
     */
    public function setReindex(?Reindex $reindex): void
    {
        $this->reindex = $reindex;
    }
}
