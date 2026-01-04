<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort;

use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Ds\Vector;

readonly class Processor
{
    public function setCorrectOrder(EntityManagerInterface $em, ChangeSet $set, Vector $vector): void
    {
        $uow = $em->getUnitOfWork();
        $meta = $set->getClassMetadata();
        $field = $set->getConfiguration()->getSortField();

        /** @var Pair $pair */
        foreach ($vector as $pair) {
            if (null === $entity = $em->find($meta->getName(), $pair->id)) {
                throw new \RuntimeException(\sprintf('Could not find entity %s withd id %s', $meta->getName(), $pair->id));
            }

            if ($entity instanceof Proxy && !$entity->__isInitialized()) {
                $entity->__load();
            }
            $em->persist($entity);

            $meta->setFieldValue($entity, $field, $pair->order);
            $uow->recomputeSingleEntityChangeSet($meta, $entity);
        }
    }
}
