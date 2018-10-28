<?php
namespace Triadev\EsMigration\Models;

class Migration
{
    /** @var int */
    private $id;
    
    /** @var string */
    private $migration;
    
    /** @var int */
    private $status;
    
    /** @var string|null */
    private $error;
    
    /** @var \DateTime */
    private $createdAt;
    
    /** @var \DateTime */
    private $updatedAt;
    
    /**
     * Migration constructor.
     * @param int $id
     * @param string $migration
     * @param int $status
     * @param null|string $error
     * @param \DateTime $createdAt
     * @param \DateTime $updatedAt
     */
    public function __construct(
        int $id,
        string $migration,
        int $status,
        ?string $error,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->migration = $migration;
        $this->status = $status;
        $this->error = $error;
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
    public function getMigration(): string
    {
        return $this->migration;
    }
    
    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
    
    /**
     * @return null|string
     */
    public function getError(): ?string
    {
        return $this->error;
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
