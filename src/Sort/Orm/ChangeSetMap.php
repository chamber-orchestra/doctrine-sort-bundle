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

class ChangeSetMap implements \IteratorAggregate
{
    public function __construct(
        private Map $map = new Map()
    ) {
    }

    public function getChangeSet(MetadataArgs $args): ChangeSet
    {
        /** @var SortConfiguration $config */
        $config = $args->configuration;

        return $this->map[$args->classMetadata->getName()] ??= new ChangeSet($args->classMetadata, $config);
    }

    public function getIterator(): \Traversable
    {
        return $this->map;
    }
}