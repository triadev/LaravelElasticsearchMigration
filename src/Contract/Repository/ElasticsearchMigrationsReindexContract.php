<?php
namespace Triadev\EsMigration\Contract\Repository;

use Triadev\EsMigration\Models\Entity\ElasticsearchMigrationsReindex;

interface ElasticsearchMigrationsReindexContract
{
    /**
     * Create
     *
     * @param int $migrationsId
     * @param string $destIndex
     * @param bool $refreshSourceIndex
     * @param array $global
     * @param array $source
     * @param array $dest
     * @return ElasticsearchMigrationsReindex
     *
     * @throws \Throwable
     */
    public function create(
        int $migrationsId,
        string $destIndex,
        bool $refreshSourceIndex = false,
        array $global = [],
        array $source = [],
        array $dest = []
    ) : ElasticsearchMigrationsReindex;
    
    /**
     * Find
     *
     * @param int $migrationsId
     * @return null|ElasticsearchMigrationsReindex
     */
    public function find(int $migrationsId) : ?ElasticsearchMigrationsReindex;
}
