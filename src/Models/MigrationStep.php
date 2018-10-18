<?php
namespace Triadev\EsMigration\Models;

class MigrationStep
{
    /** @var int */
    private $id;
    
    /** @var string */
    private $type;
    
    /** @var int */
    private $status;
    
    /** @var string */
    private $error;
    
    /** @var array */
    private $params;
    
    /** @var \DateTime */
    private $createdAt;
    
    /** @var \DateTime */
    private $updatedAt;
    
    /**
     * MigrationStep constructor.
     * @param int $id
     * @param string $type
     * @param int $status
     * @param null|string $error
     * @param array $params
     * @param \DateTime $createdAt
     * @param \DateTime $updatedAt
     */
    public function __construct(
        int $id,
        string $type,
        int $status,
        ?string $error,
        array $params,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->status = $status;
        $this->error = $error;
        $this->params = $params;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
    
    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
    
    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
    
    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
}
