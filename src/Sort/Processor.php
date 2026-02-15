<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort;

use ChamberOrchestra\DoctrineSortBundle\Exception\RuntimeException;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ds\Vector;

readonly class Processor
{
    public function setCorrectOrder(EntityManagerInterface $em, ChangeSet $set, Vector $vector): void
    {
        if ($vector->isEmpty()) {
            return;
        }

        $uow = $em->getUnitOfWork();
        $meta = $set->getClassMetadata();
        $field = $set->getConfiguration()->getSortField();
        $idField = $meta->getIdentifier()[0];

        $entities = $this->loadEntities($em, $meta, $idField, $vector);

        /** @var Pair $pair */
        foreach ($vector as $pair) {
            if (!isset($entities[$pair->id])) {
                throw new RuntimeException(\sprintf('Could not find entity %s with id %s', $meta->getName(), $pair->id));
            }

            $entity = $entities[$pair->id];

            $meta->setFieldValue($entity, $field, $pair->order);
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }

    /**
     * @return array<int|string, object>
     */
    private function loadEntities(EntityManagerInterface $em, ClassMetadata $meta, string $idField, Vector $vector): array
    {
        $ids = [];
        /** @var Pair $pair */
        foreach ($vector as $pair) {
            $ids[] = $pair->id;
        }

        // Batch-load existing entities from database (1 query instead of N)
        $dql = \sprintf('SELECT e FROM %s e WHERE e.%s IN (:ids)', $meta->getName(), $idField);
        $result = $em->createQuery($dql)->setParameter('ids', $ids)->getResult();

        $map = [];
        foreach ($result as $entity) {
            $map[$meta->getFieldValue($entity, $idField)] = $entity;
        }

        // Newly persisted entities (scheduled for insertion) are not yet in the database.
        // Fall back to the identity map via find() — this is a cheap in-memory lookup.
        foreach ($ids as $id) {
            if (!isset($map[$id])) {
                $map[$id] = $em->find($meta->getName(), $id);
            }
        }

        return $map;
    }
}
