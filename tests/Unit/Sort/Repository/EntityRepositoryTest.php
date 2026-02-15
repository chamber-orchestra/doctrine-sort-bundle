<?php

declare(strict_types=1);

namespace Tests\Unit\Sort\Repository;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Repository\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

class EntityRepositoryEntity
{
    public int $id = 1;
}

final class EntityRepositoryTest extends TestCase
{
    public function testRepositoryInstantiatesWithDependencies(): void
    {
        $metadata = new ClassMetadata(EntityRepositoryEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => EntityRepositoryEntity::class,
        ]);

        $em = $this->createStub(EntityManagerInterface::class);

        $repository = new EntityRepository($em, $metadata, $config);

        self::assertInstanceOf(EntityRepository::class, $repository);
    }

    public function testGetMaxSortOrderCachesAndIncrements(): void
    {
        $metadata = new ClassMetadata(EntityRepositoryEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => EntityRepositoryEntity::class,
        ]);

        $query = $this->createStub(Query::class);
        $query->method('getSingleScalarResult')->willReturn(5);

        $qb = $this->createStub(QueryBuilder::class);
        $qb->method('from')->willReturnSelf();
        $qb->method('select')->willReturnSelf();
        $qb->method('setMaxResults')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('createQueryBuilder')->willReturn($qb);

        $repository = new EntityRepository($em, $metadata, $config);

        self::assertSame(5, $repository->getMaxSortOrder([], false));
        self::assertSame(6, $repository->getMaxSortOrder([]));
        self::assertSame(6, $repository->getMaxSortOrder([], false));
    }

    public function testNullGroupingConditionUsesIsNull(): void
    {
        $metadata = new ClassMetadata(EntityRepositoryEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->identifier = ['id'];

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => EntityRepositoryEntity::class,
        ]);

        $expr = $this->createMock(Expr::class);
        $expr->method('isNull')->with('n.parent')->willReturn('n.parent IS NULL');

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects(self::once())->method('expr')->willReturn($expr);
        $qb->expects(self::once())->method('andWhere')->with('n.parent IS NULL')->willReturnSelf();

        $em = $this->createStub(EntityManagerInterface::class);
        $repository = new EntityRepository($em, $metadata, $config);

        $invoker = function (QueryBuilder $qb, array $condition): void {
            $this->addGroupingCondition($qb, $condition);
        };

        $invoker->bindTo($repository, $repository::class)($qb, ['parent' => null]);
    }
}
