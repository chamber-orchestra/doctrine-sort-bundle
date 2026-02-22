<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort\Orm;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use Ds\Map;

/**
 * @implements \IteratorAggregate<string, ChangeSet>
 */
class ChangeSetMap implements \IteratorAggregate
{
    /** @var Map<string, ChangeSet> */
    private Map $map;

    public function __construct()
    {
        $this->map = new Map();
    }

    public function getChangeSet(MetadataArgs $args): ChangeSet
    {
        /** @var SortConfiguration $config */
        $config = $args->configuration;
        $key = $args->getClassMetadata()->getName();

        if (!$this->map->hasKey($key)) {
            $this->map->put($key, new ChangeSet($args->getClassMetadata(), $config));
        }

        return $this->map->get($key);
    }

    /**
     * @return \Traversable<string, ChangeSet>
     */
    public function getIterator(): \Traversable
    {
        return $this->map;
    }
}
