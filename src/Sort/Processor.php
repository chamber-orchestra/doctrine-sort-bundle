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
    /**
     * @param Vector<Pair> $vector
     */
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
     * @param Vector<Pair> $vector
     * @return array<int|string, object>
     */
    private function loadEntities(EntityManagerInterface $em, ClassMetadata $meta, string $idField, Vector $vector): array
    {
        /** @var list<int|string> $ids */
        $ids = [];
        /** @var Pair $pair */
        foreach ($vector as $pair) {
            $ids[] = $pair->id;
        }

        // Batch-load existing entities from database (1 query instead of N)
        $dql = \sprintf('SELECT e FROM %s e WHERE e.%s IN (:ids)', $meta->getName(), $idField);
        /** @var list<object> $result */
        $result = $em->createQuery($dql)->setParameter('ids', $ids)->getResult();

        /** @var array<int|string, object> $map */
        $map = [];
        foreach ($result as $entity) {
            /** @var int|string $id */
            $id = $meta->getFieldValue($entity, $idField);
            $map[$id] = $entity;
        }

        // Newly persisted entities (scheduled for insertion) are not yet in the database.
        // Fall back to the identity map via find() — this is a cheap in-memory lookup.
        foreach ($ids as $id) {
            if (!isset($map[$id])) {
                $entity = $em->find($meta->getName(), $id);
                if (null !== $entity) {
                    $map[$id] = $entity;
                }
            }
        }

        return $map;
    }
}
