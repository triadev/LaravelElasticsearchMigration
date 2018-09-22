<?php
namespace Triadev\EsMigration\Business\Factory;

use Triadev\EsMigration\Models\Migrations\Alias;
use Triadev\EsMigration\Models\Migrations\CreateIndex;
use Triadev\EsMigration\Models\Migrations\DeleteByQuery;
use Triadev\EsMigration\Models\Migrations\DeleteIndex;
use Triadev\EsMigration\Models\Migrations\Reindex;
use Triadev\EsMigration\Models\Migrations\UpdateByQuery;
use Triadev\EsMigration\Models\Migrations\UpdateIndex;

class MigrationBuilder
{
    /**
     * Migration: createIndex
     *
     * @param string $index
     * @param array $mappings
     * @param array|null $settings
     * @return CreateIndex
     */
    public static function createIndex(
        string $index,
        array $mappings,
        ?array $settings = null
    ) : CreateIndex {
        return (new CreateIndex($index, $mappings))->setSettings($settings);
    }
    
    /**
     * Migration: updateIndex
     *
     * @param string $index
     * @param array|null $mappings
     * @param array|null $settings
     * @param bool $closeIndex
     * @return UpdateIndex
     */
    public static function updateIndex(
        string $index,
        ?array $mappings = null,
        ?array $settings = null,
        bool $closeIndex = false
    ) : UpdateIndex {
        return (new UpdateIndex($index))
            ->setMappings($mappings)
            ->setSettings($settings)
            ->setCloseIndex($closeIndex);
    }
    
    /**
     * Migration: deleteIndex
     *
     * @param string $index
     * @return DeleteIndex
     */
    public static function deleteIndex(
        string $index
    ) : DeleteIndex {
        return (new DeleteIndex($index));
    }
    
    /**
     * Migration: alias
     *
     * @param string $index
     * @param array $add
     * @param array $remove
     * @param array $removeIndices
     * @return Alias
     */
    public static function alias(
        string $index,
        array $add = [],
        array $remove = [],
        array $removeIndices = []
    ) : Alias {
        return (new Alias($index))
            ->setAdd($add)
            ->setRemove($remove)
            ->setRemoveIndices($removeIndices);
    }
    
    /**
     * Migration: deleteByQuery
     *
     * @param string $index
     * @param array $query
     * @param null|string $type
     * @param array $options
     * @return DeleteByQuery
     */
    public static function deleteByQuery(
        string $index,
        array $query,
        ?string $type = null,
        array $options = []
    ) : DeleteByQuery {
        return (new DeleteByQuery($index, $query))
            ->setType($type)
            ->setOptions($options);
    }
    
    /**
     * Migration: updateByQuery
     *
     * @param string $index
     * @param array $query
     * @param null|string $type
     * @param null|array $script
     * @param array $options
     * @return UpdateByQuery
     */
    public static function updateByQuery(
        string $index,
        array $query,
        ?string $type = null,
        ?array $script = null,
        array $options = []
    ) : UpdateByQuery {
        return (new UpdateByQuery($index, $query))
            ->setType($type)
            ->setScript($script)
            ->setOptions($options);
    }
    
    /**
     * Migration: reindex
     *
     * @param string $index
     * @param string $destIndex
     * @param bool $refreshSourceIndex
     * @param array $global
     * @param array $source
     * @param array $dest
     * @return Reindex
     */
    public static function reindex(
        string $index,
        string $destIndex,
        bool $refreshSourceIndex = false,
        array $global = [],
        array $source = [],
        array $dest = []
    ) : Reindex {
        return (new Reindex($index, $destIndex))
            ->setRefreshSourceIndex($refreshSourceIndex)
            ->setGlobal($global)
            ->setSource($source)
            ->setDest($dest);
    }
}
