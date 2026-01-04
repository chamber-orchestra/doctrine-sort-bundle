<?php

declare(strict_types=1);

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

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getUnitOfWork')->willReturn($uow);
        $em->method('find')->with(ProcessorEntity::class, 1)->willReturn($entity);
        $em->expects(self::once())->method('persist')->with($entity);

        $vector = new Vector([new Pair(1, 5)]);

        $processor = new Processor();
        $processor->setCorrectOrder($em, $changeSet, $vector);

        self::assertSame(5, $entity->sortOrder);
    }
}
