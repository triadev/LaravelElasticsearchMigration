<?php
namespace Triadev\EsMigration\Models\Migrations;

class Reindex extends Migration
{
    /** @var string */
    private $destIndex;
    
    /** @var bool */
    private $refreshSourceIndex = false;
    
    /** @var array */
    private $global = [];
    
    /** @var array */
    private $source = [];
    
    /** @var array */
    private $dest = [];
    
    /**
     * Reindex constructor.
     * @param string $index
     * @param string $destIndex
     */
    public function __construct(string $index, string $destIndex)
    {
        parent::__construct($index);
        
        $this->destIndex = $destIndex;
    }
    
    /**
     * @return string
     */
    public function getDestIndex(): string
    {
        return $this->destIndex;
    }
    
    /**
     * @return bool
     */
    public function isRefreshSourceIndex(): bool
    {
        return $this->refreshSourceIndex;
    }
    
    /**
     * @param bool $refreshSourceIndex
     * @return Reindex
     */
    public function setRefreshSourceIndex(bool $refreshSourceIndex): Reindex
    {
        $this->refreshSourceIndex = $refreshSourceIndex;
        
        return $this;
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
     * @return Reindex
     */
    public function setGlobal(array $global): Reindex
    {
        $this->global = $global;
        
        return $this;
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
     * @return Reindex
     */
    public function setSource(array $source): Reindex
    {
        $this->source = $source;
        
        return $this;
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
     * @return Reindex
     */
    public function setDest(array $dest): Reindex
    {
        $this->dest = $dest;
        
        return $this;
    }
}
