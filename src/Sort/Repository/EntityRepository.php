<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChamberOrchestra\DoctrineSortBundle\Sort\Repository;

use ChamberOrchestra\MetadataBundle\Mapping\ORM\MetadataConfigurationInterface;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Util\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Ds\Vector;

class EntityRepository
{
    private array $maxSortOrder = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClassMetadata $metadata,
        private readonly MetadataConfigurationInterface $configuration,
    ) {
    }

    public function getMaxSortOrder(array $condition, bool $increase = true): int
    {
        if (isset($this->maxSortOrder[$hash = Utils::hash($condition)])) {
            return $increase ? ++$this->maxSortOrder[$hash] : $this->maxSortOrder[$hash];
        }

        $qb = $this->createQueryBuilder('n');
        $qb
            ->select(\sprintf('MAX(n.%s)', $this->configuration->getSortField()))
            ->setMaxResults(1);

        $this->addGroupingCondition($qb, $condition);

        return $this->maxSortOrder[$hash] = (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function getCollection(array $condition, int $min, int $max): Vector
    {
        $meta = $this->metadata;
        $idField = \current($meta->getIdentifier());
        $field = $this->configuration->getSortField();

        $qb = $this->createQueryBuilder('n');
        // allow using SQL index
        $this->addGroupingCondition($qb, $condition);
        $qb
            ->select(\sprintf('NEW %s(n.%s, n.%s)', Pair::class, $idField, $field))
            ->andWhere($qb->expr()->between(\sprintf('n.%s', $field), ':left', ':right'))
            ->setParameter('left', $min)
            ->setParameter('right', $max)
            ->orderBy(\sprintf('n.%s', $field), 'ASC');

        return new Vector($qb->getQuery()->useQueryCache(true)->getResult());
    }

    private function createQueryBuilder(string $alias): QueryBuilder
    {
        return $this->em->createQueryBuilder()->from($this->configuration->getEntityName(), $alias);
    }

    private function addGroupingCondition(QueryBuilder $qb, array $condition): void
    {
        $i = 0;
        foreach ($condition as $key => $value) {
            if (null === $value) {
                $qb->andWhere($qb->expr()->isNull(\sprintf('n.%s', $key)));
                continue;
            }
            $qb
                ->andWhere($qb->expr()->eq(\sprintf('n.%s', $key), ':'.($param = 'group_param'.(++$i))))
                ->setParameter($param, $value);
        }
    }
}
