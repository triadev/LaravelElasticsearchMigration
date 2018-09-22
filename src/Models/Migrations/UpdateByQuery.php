<?php
namespace Triadev\EsMigration\Models\Migrations;

class UpdateByQuery extends Migration
{
    /** @var array */
    private $query;
    
    /** @var string|null */
    private $type;
    
    /** @var array|null */
    private $script;
    
    /** @var array */
    private $options = [];
    
    /**
     * UpdateByQuery constructor.
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
     * @return UpdateByQuery
     */
    public function setType(?string $type): UpdateByQuery
    {
        $this->type = $type;
        
        return $this;
    }
    
    /**
     * @return array|null
     */
    public function getScript(): ?array
    {
        return $this->script;
    }
    
    /**
     * @param array|null $script
     *
     * @return UpdateByQuery
     */
    public function setScript(?array $script): UpdateByQuery
    {
        $this->script = $script;
        
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
     * @return UpdateByQuery
     */
    public function setOptions(array $options): UpdateByQuery
    {
        $this->options = $options;
        
        return $this;
    }
}
