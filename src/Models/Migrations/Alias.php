<?php
namespace Triadev\EsMigration\Models\Migrations;

class Alias extends Migration
{
    /** @var array */
    private $add = [];
    
    /** @var array */
    private $remove = [];
    
    /** @var array */
    private $removeIndices = [];
    
    /**
     * Alias constructor.
     * @param string $index
     */
    public function __construct(string $index)
    {
        parent::__construct($index);
    }
    
    /**
     * @return array
     */
    public function getAdd(): array
    {
        return $this->add;
    }
    
    /**
     * @param array $add
     * @return Alias
     */
    public function setAdd(array $add): Alias
    {
        $this->add = $add;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getRemove(): array
    {
        return $this->remove;
    }
    
    /**
     * @param array $remove
     * @return Alias
     */
    public function setRemove(array $remove): Alias
    {
        $this->remove = $remove;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getRemoveIndices(): array
    {
        return $this->removeIndices;
    }
    
    /**
     * @param array $removeIndices
     * @return Alias
     */
    public function setRemoveIndices(array $removeIndices): Alias
    {
        $this->removeIndices = $removeIndices;
        
        return $this;
    }
}
