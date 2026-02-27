<?php

declare(strict_types=1);

/*
 * This file is part of the ChamberOrchestra package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Sort;

use ChamberOrchestra\DoctrineSortBundle\Mapping\Configuration\SortConfiguration;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\ChangeSet;
use ChamberOrchestra\DoctrineSortBundle\Sort\Orm\Pair;
use ChamberOrchestra\DoctrineSortBundle\Sort\Processor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use Ds\Vector;
use PHPUnit\Framework\TestCase;

class ProcessorEntity
{
    public int $id = 1;
    public int $sortOrder = 1;
}

final class ProcessorTest extends TestCase
{
    public function testSetCorrectOrderUpdatesEntity(): void
    {
        $metadata = new ClassMetadata(ProcessorEntity::class);
        $metadata->initializeReflection(new RuntimeReflectionService());
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'id' => true]);
        $metadata->mapField(['fieldName' => 'sortOrder', 'type' => 'integer']);
        $metadata->identifier = ['id'];
        $metadata->wakeupReflection(new RuntimeReflectionService());

        $config = new SortConfiguration();
        $config->mapField('sortOrder', [
            'groupBy' => [],
            'evictCollections' => [],
            'evictRegions' => [],
            'entityName' => ProcessorEntity::class,
        ]);

        $changeSet = new ChangeSet($metadata, $config);
        $entity = new ProcessorEntity();

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $entity);

        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('getResult')->willReturn([$entity]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getUnitOfWork')->willReturn($uow);
        $em->method('createQuery')->willReturn($query);

        $vector = new Vector([new Pair(1, 5)]);

        $processor = new Processor();
        $processor->setCorrectOrder($em, $changeSet, $vector);

        self::assertSame(5, $entity->sortOrder);
    }
}
