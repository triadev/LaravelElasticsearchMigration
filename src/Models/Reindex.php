<?php
namespace Triadev\EsMigration\Models;

class Reindex
{
    /** @var string */
    private $index;
    
    /** @var bool */
    private $refresh = false;
    
    /** @var string|null */
    private $versionType;
    
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
     * @return null|string
     */
    public function getVersionType(): ?string
    {
        return $this->versionType;
    }
    
    /**
     * @param null|string $versionType
     */
    public function setVersionType(?string $versionType): void
    {
        $this->versionType = $versionType;
    }
}
