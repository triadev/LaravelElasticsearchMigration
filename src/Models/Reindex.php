<?php
namespace Triadev\EsMigration\Models;

class Reindex
{
    /** @var string */
    private $index;
    
    /** @var bool */
    private $refresh = false;
    
    /** @var array */
    private $global = [];
    
    /** @var array */
    private $source = [];
    
    /** @var array */
    private $dest = [];
    
    /**
     * Reindex constructor.
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
    
    /**
     * @return bool
     */
    public function isRefresh(): bool
    {
        return $this->refresh;
    }
    
    /**
     * @param bool $refresh
     */
    public function setRefresh(bool $refresh): void
    {
        $this->refresh = $refresh;
    }
    
    /**
     * @return array
     */
    public function getGlobal(): array
    {
        return $this->global;
    }
    
    /**
     * @param array $global
     */
    public function setGlobal(array $global): void
    {
        $this->global = $global;
    }
    
    /**
     * @return array
     */
    public function getSource(): array
    {
        return $this->source;
    }
    
    /**
     * @param array $source
     */
    public function setSource(array $source): void
    {
        $this->source = $source;
    }
    
    /**
     * @return array
     */
    public function getDest(): array
    {
        return $this->dest;
    }
    
    /**
     * @param array $dest
     */
    public function setDest(array $dest): void
    {
        $this->dest = $dest;
    }
}
