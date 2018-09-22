<?php
namespace Triadev\EsMigration\Models\Migrations;

class DeleteByQuery extends Migration
{
    /** @var array */
    private $query;
    
    /** @var string|null */
    private $type;
    
    /** @var array */
    private $options = [];
    
    /**
     * DeleteByQuery constructor.
     * @param string $index
     * @param array $query
     */
    public function __construct(string $index, array $query)
    {
        parent::__construct($index);
        
        $this->query = $query;
    }
    
    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }
    
    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }
    
    /**
     * @param null|string $type
     *
     * @return DeleteByQuery
     */
    public function setType(?string $type): DeleteByQuery
    {
        $this->type = $type;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
    
    /**
     * @param array $options [
     *      KEY => VALUE,
     *      ...
     * ]
     *
     * @return DeleteByQuery
     */
    public function setOptions(array $options): DeleteByQuery
    {
        $this->options = $options;
        
        return $this;
    }
}
