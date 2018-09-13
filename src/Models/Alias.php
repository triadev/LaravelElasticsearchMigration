<?php
namespace Triadev\EsMigration\Models;

class Alias
{
    /** @var array */
    private $add = [];
    
    /** @var array */
    private $remove = [];
    
    /** @var array */
    private $removeIndex = [];
    
    /**
     * @return array
     */
    public function getAdd(): array
    {
        return $this->add;
    }
    
    /**
     * @param array $add
     */
    public function setAdd(array $add): void
    {
        $this->add = $add;
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
     */
    public function setRemove(array $remove): void
    {
        $this->remove = $remove;
    }
    
    /**
     * @return array
     */
    public function getRemoveIndex(): array
    {
        return $this->removeIndex;
    }
    
    /**
     * @param array $removeIndex
     */
    public function setRemoveIndex(array $removeIndex): void
    {
        $this->removeIndex = $removeIndex;
    }
}
