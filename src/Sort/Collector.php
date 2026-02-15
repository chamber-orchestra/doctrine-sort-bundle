<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSetMap;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Helper\DiffHelper;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;

readonly class Collector
{
    public function __construct(
        private RepositoryFactory $factory,
        private DiffHelper $helper
    ) {
    }

    public function addUpdateIfNeeded(ChangeSetMap $map, MetadataArgs $args): void
    {
        if (!$this->helper->hasChangedFields($args)) {
            return;
        }

        [$oldCondition, $newCondition] = $this->helper->getGroupingFieldChangeSet($args);
        [$oldOrder, $newOrder] = $this->helper->getSortFieldChangeSet($args);

        $newOrder = $this->fixOrder($args, $newOrder, $newCondition);

        $set = $map->getChangeSet($args);
        $set->addDeletion($args->entity, $oldOrder, $oldCondition);
        $set->addInsertion($args->entity, $newOrder, $newCondition);
    }

    public function addInsertion(ChangeSetMap $map, MetadataArgs $args): void
    {
        [, $order] = $this->helper->getSortFieldChangeSet($args);
        [, $condition] = $this->helper->getGroupingFieldChangeSet($args);

        $order = $this->fixOrder($args, $order, $condition);

        $set = $map->getChangeSet($args);
        $set->addInsertion($args->entity, $order, $condition);
    }

    public function addDeletion(ChangeSetMap $map, MetadataArgs $args): void
    {
        [$order,] = $this->helper->getSortFieldChangeSet($args);
        [$condition,] = $this->helper->getGroupingFieldChangeSet($args);

        $set = $map->getChangeSet($args);
        $set->addDeletion($args->entity, $order, $condition);
    }

    private function fixOrder(MetadataArgs $args, int|null $order, array $condition): int
    {
        $meta = $args->getClassMetadata();
        /** @var SortConfiguration $config */
        $config = $args->configuration;
        $er = $this->factory->getRepository($meta, $config);

        $maxOrder = $er->getMaxSortOrder($condition);

        return (null === $order || 0 === $order)
            ? $maxOrder + 1
            : \max(1, \min($maxOrder + 1, $order));
    }
}
