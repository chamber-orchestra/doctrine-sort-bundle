<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Helper;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\MetadataBundle\Helper\MetadataArgs;
use Doctrine\ORM\EntityManagerInterface;

readonly class DiffHelper
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function getSortFieldChangeSet(MetadataArgs $args): array
    {
        $uow = $this->em->getUnitOfWork();
        $set = $uow->getEntityChangeSet($entity = $args->entity);
        /** @var SortConfiguration $config */
        $config = $args->configuration;

        if (isset($set[$sortField = $config->getSortField()])) {
            return $set[$sortField];
        }

        return [$value = $args->getClassMetadata()->getFieldValue($entity, $sortField), $value];
    }

    public function getGroupingFieldChangeSet(MetadataArgs $args): array
    {
        $uow = $this->em->getUnitOfWork();
        $set = $uow->getEntityChangeSet($entity = $args->entity);
        /** @var SortConfiguration $config */
        $config = $args->configuration;

        $oldCondition = $newCondition = [];
        foreach ($config->getGroupingFields() as $field) {
            if (isset($set[$field])) {
                [$old, $new] = $set[$field];
                $oldCondition[$field] = $old;
                $newCondition[$field] = $new;

                continue;
            }

            $value = $args->getClassMetadata()->getFieldValue($entity, $field);
            $oldCondition[$field] = $newCondition[$field] = $value;
        }

        return [$oldCondition, $newCondition];
    }

    public function hasChangedFields(MetadataArgs $args): bool
    {
        $uow = $this->em->getUnitOfWork();
        $set = $uow->getEntityChangeSet($args->entity);
        /** @var SortConfiguration $config */
        $config = $args->configuration;

        if (isset($set[$config->getSortField()])) {
            return true;
        }

        return \array_any($config->getGroupingFields(), fn ($field) => isset($set[$field]));

    }
}
